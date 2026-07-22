<?php
namespace App\Services\Operasional;

use App\Models\Transaksi;
use App\Models\BuktiTransaksi;
use App\Models\Jurnal;
use App\Models\DetailJurnal;
use App\Models\Periode;
use App\Http\Requests\StoreTransaksiRequest;
use App\Http\Requests\UpdateTransaksiRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TransaksiService
{
    /**
     * Gerbang siklus akuntansi: transaksi (yang otomatis menghasilkan jurnal
     * umum) hanya boleh dicatat setelah saldo awal / jurnal pembuka dibuat dan
     * diposting. Pengecekan ringan berbasis exists().
     */
    private function pastikanPembukaSiap(): void
    {
        $adaPembukaPosted = Jurnal::where('jenis_jurnal', 'PEMBUKA')
            ->where('status', 'POSTED')
            ->exists();

        if (!$adaPembukaPosted) {
            throw new \RuntimeException(
                'Belum ada saldo awal (jurnal pembuka) yang diposting. '
                . 'Buat dan posting jurnal pembuka terlebih dahulu sebelum mencatat transaksi.'
            );
        }
    }

    /**
     * Immutability periode: transaksi yang tanggalnya jatuh pada periode yang
     * sudah ditutup tidak boleh dicatat, diubah, atau dihapus, agar laporan
     * periode yang sudah final tidak berubah retroaktif (menegakkan cut-off).
     */
    private function pastikanTanggalTidakDiPeriodeTertutup(?string $tanggal): void
    {
        if (!$tanggal) {
            return;
        }

        $periodeTertutup = Periode::where('status', false)
            ->whereDate('tanggal_awal', '<=', $tanggal)
            ->whereDate('tanggal_akhir', '>=', $tanggal)
            ->first();

        if ($periodeTertutup) {
            throw new \RuntimeException(
                "Periode {$periodeTertutup->nama_periode} sudah ditutup. "
                . 'Transaksi dengan tanggal pada periode tersebut tidak dapat dicatat, '
                . 'diubah, atau dihapus. Gunakan jurnal koreksi pada periode berjalan bila perlu.'
            );
        }
    }

    public function simpanTransaksiBaru(StoreTransaksiRequest $request, bool $force = false): Transaksi
    {
        $this->pastikanPembukaSiap();
        $this->pastikanTanggalTidakDiPeriodeTertutup($request->tanggal_transaksi);

        return DB::transaction(function () use ($request, $force) {

            $entries = $request->input('jurnal', []);
            $jumlah  = $this->hitungTotalDebit($entries);

            if (!$force) {
                $duplikat = Transaksi::with(['kategoriTransaksi', 'dompet'])
                    ->whereDate('tanggal_transaksi', $request->tanggal_transaksi)
                    ->where('jumlah', $jumlah)
                    ->where('jenis_transaksi', $request->jenis_transaksi)
                    ->where('dompet_id', $request->dompet_id)
                    ->first();

                if ($duplikat) {
                    throw new \RuntimeException(
                        'DUPLIKAT_WARNING:' . json_encode([
                            'tanggal'   => $duplikat->tanggal_transaksi->translatedFormat('d M Y'),
                            'jumlah'    => number_format($duplikat->jumlah, 0, ',', '.'),
                            'jenis'     => $duplikat->jenis_transaksi,
                            'deskripsi' => $duplikat->deskripsi ?? '-',
                            'kategori'  => $duplikat->kategoriTransaksi?->nama_kategori ?? '-',
                            'dompet'    => $duplikat->dompet?->nama_dompet ?? '-',
                        ])
                    );
                }
            }

            // 1. Simpan transaksi (jumlah dihitung dari total debit jurnal)
            $transaksi = Transaksi::create([
                'dompet_id'             => $request->dompet_id,
                'kegiatan_id'           => null,
                'pengguna_id'               => Auth::id(),
                'kategori_transaksi_id' => $request->kategori_transaksi_id,
                'tanggal_transaksi'     => $request->tanggal_transaksi,
                'jenis_transaksi'       => $request->jenis_transaksi,
                'jumlah'                => $jumlah,
                'deskripsi'             => $request->deskripsi,
                'catatan'               => $request->catatan,
                'status_persetujuan'       => null,
                'status_jurnal'         => 'MAPPED',
            ]);

            // 2. Jurnal entri multi debit & kredit
            $this->buatJurnalUmum($transaksi, $entries, $request->deskripsi);

            $this->unggahBuktiTransaksi($transaksi, $request->file('bukti_transaksi') ?? []);

            if ($request->boolean('is_aset')) {
                $this->simpanAsetDariTransaksi($transaksi, array_merge($request->all(), ['jumlah' => $jumlah]));
            }

            return $transaksi->load('buktiTransaksi', 'jurnal.detailJurnal.akun', 'aset');
        });
    }

    public function perbaruiTransaksi(UpdateTransaksiRequest $request, Transaksi $transaksi): Transaksi
    {
        $this->pastikanTanggalTidakDiPeriodeTertutup($transaksi->tanggal_transaksi?->toDateString());
        $this->pastikanTanggalTidakDiPeriodeTertutup($request->tanggal_transaksi);

        return DB::transaction(function () use ($request, $transaksi) {

            $entries = $request->input('jurnal', []);
            $jumlah  = $this->hitungTotalDebit($entries);

            $transaksi->update([
                'dompet_id'         => $request->dompet_id,
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'jenis_transaksi'   => $request->jenis_transaksi,
                'jumlah'            => $jumlah,
                'deskripsi'         => $request->deskripsi,
                'catatan'           => $request->catatan,
                'status_jurnal'       => 'MAPPED',
            ]);

            // Hapus jurnal & detail lama, ganti dengan yang baru
            $jurnalLama = $transaksi->jurnal()->where('jenis_jurnal', 'UMUM')->first();
            if ($jurnalLama) {
                $jurnalLama->detailJurnal()->delete();
                $jurnalLama->delete();
            }

            $this->buatJurnalUmum($transaksi, $entries, $request->deskripsi);

            $this->unggahBuktiTransaksi($transaksi, $request->file('bukti_transaksi') ?? []);

            return $transaksi->fresh(['buktiTransaksi', 'jurnal.detailJurnal.akun']);
        });
    }

    public function hapusTransaksi(Transaksi $transaksi): void
    {
        $this->pastikanTanggalTidakDiPeriodeTertutup($transaksi->tanggal_transaksi?->toDateString());

        DB::transaction(function () use ($transaksi) {
            foreach ($transaksi->buktiTransaksi as $bukti) {
                Storage::disk('public')->delete($bukti->path_file);
            }
            foreach ($transaksi->jurnal as $jurnal) {
                $jurnal->detailJurnal()->delete();
                $jurnal->delete();
            }
            $transaksi->delete();
        });
    }

    public function simpanTransaksiHasilImpor(array $sessionData, array $klasifikasi): array
    {
        $rowsMap = collect($sessionData['rows'])->keyBy('no_referensi');
        $klasMap = collect($klasifikasi)->keyBy('no_referensi');
        $jenis   = $sessionData['jenis_transaksi'];

        $tersimpan    = 0;
        $dilewati     = 0;
        $duplikat     = 0;
        $gagalPeriode = [];
        $gagalBalance = [];
        $gagalJenis   = [];

        foreach ($rowsMap as $ref => $row) {
            if ($row['is_duplikat']) {
                $duplikat++;
                continue;
            }

            // Baris debit/kredit tidak sesuai jenis_transaksi
            if (($row['jenis_transaksi'] ?? null) !== $jenis) {
                $dilewati++;
                $gagalJenis[] = $ref;
                continue;
            }

            $klas = $klasMap->get($ref);

            if (!$klas || empty($klas['entries'])) {
                $dilewati++;
                continue;
            }

            $sudahAda = Transaksi::where('no_referensi', $row['no_referensi'])->exists();
            if ($sudahAda) {
                $duplikat++;
                continue;
            }

            $tanggal = substr($row['waktu_transaksi'] ?? now()->toDateString(), 0, 10);

            // Cek periode aktif untuk tanggal baris ini
            $periode = Periode::aktif()
                ->where('tanggal_awal', '<=', $tanggal)
                ->where('tanggal_akhir', '>=', $tanggal)
                ->first();

            if (!$periode) {
                $dilewati++;
                $gagalPeriode[$tanggal] = ($gagalPeriode[$tanggal] ?? 0) + 1;
                continue;
            }

            // Validasi balance: total debit = total kredit = jumlah mutasi bank
            $totalDebit  = collect($klas['entries'])
                ->filter(fn($e) => strtoupper($e['tipe'] ?? '') === 'DEBIT')
                ->sum(fn($e) => (float) ($e['nominal'] ?? 0));
            $totalKredit = collect($klas['entries'])
                ->filter(fn($e) => strtoupper($e['tipe'] ?? '') === 'KREDIT')
                ->sum(fn($e) => (float) ($e['nominal'] ?? 0));

            if (abs($totalDebit - $totalKredit) > 0.5 || abs($totalDebit - (float) $row['jumlah']) > 0.5) {
                $dilewati++;
                $gagalBalance[] = $ref;
                continue;
            }

            DB::transaction(function () use ($row, $klas, $jenis, $tanggal, $sessionData, $periode) {
                $transaksi = Transaksi::create([
                    'dompet_id'             => $sessionData['dompet_id'],
                    'pengguna_id'               => Auth::id(),
                    'kategori_transaksi_id' => null,
                    'tanggal_transaksi'     => $tanggal,
                    'jenis_transaksi'       => $jenis,
                    'jumlah'                => $row['jumlah'],
                    'deskripsi'             => $row['deskripsi'],
                    'no_referensi'          => $row['no_referensi'],
                    'catatan'               => null,
                    'status_persetujuan'       => null,
                    'status_jurnal'         => 'MAPPED',
                ]);

                $this->buatJurnalUmum($transaksi, $klas['entries'], $row['deskripsi'], $periode);
            });

            $tersimpan++;
        }

        return [
            'tersimpan'    => $tersimpan,
            'dilewati'     => $dilewati,
            'duplikat'     => $duplikat,
            'total'        => $rowsMap->count(),
            'gagalPeriode' => $gagalPeriode,
            'gagalBalance' => $gagalBalance,
            'gagalJenis'   => $gagalJenis,
        ];
    }

    /**
     * Buat jurnal umum dengan banyak baris debit/kredit (general ledger entries).
     * $entries: [['akun_id' => int, 'tipe' => 'DEBIT'|'KREDIT', 'nominal' => float], ...]
     */
    private function buatJurnalUmum(
        Transaksi $transaksi,
        array $entries,
        ?string $deskripsi = null,
        ?Periode $periode = null
    ): Jurnal {
        if (count($entries) < 2) {
            throw new \RuntimeException('Jurnal minimal harus memiliki 1 baris debit dan 1 baris kredit.');
        }

        $totalDebit  = 0;
        $totalKredit = 0;

        foreach ($entries as $e) {
            $tipe    = strtoupper($e['tipe'] ?? '');
            $nominal = (float) ($e['nominal'] ?? 0);

            if ($tipe === 'DEBIT') {
                $totalDebit += $nominal;
            } elseif ($tipe === 'KREDIT') {
                $totalKredit += $nominal;
            } else {
                throw new \RuntimeException("Tipe jurnal tidak valid: {$tipe}");
            }
        }

        if (abs($totalDebit - $totalKredit) > 0.5) {
            throw new \RuntimeException(
                'Jurnal tidak balance: total debit Rp' . number_format($totalDebit, 0, ',', '.') .
                ' tidak sama dengan total kredit Rp' . number_format($totalKredit, 0, ',', '.')
            );
        }

        $periode = $periode ?? Periode::aktif()
            ->where('tanggal_awal', '<=', $transaksi->tanggal_transaksi)
            ->where('tanggal_akhir', '>=', $transaksi->tanggal_transaksi)
            ->first();

        if (!$periode) {
            throw new \RuntimeException(
                'Tidak ada periode aktif untuk tanggal ' . $transaksi->tanggal_transaksi->translatedFormat('d F Y')
            );
        }

        $jurnal = Jurnal::create([
            'periode_id'   => $periode->id,
            'transaksi_id' => $transaksi->id,
            'jenis_jurnal' => 'UMUM',
            'tanggal'      => $transaksi->tanggal_transaksi,
            'deskripsi'    => $deskripsi ?? "Jurnal untuk transaksi #{$transaksi->id}",
            'status'       => 'DRAFT',
        ]);

        $rows = [];
        foreach ($entries as $e) {
            $rows[] = [
                'jurnal_id'  => $jurnal->id,
                'akun_id'    => $e['akun_id'],
                'tipe'       => strtoupper($e['tipe']),
                'nominal'    => $e['nominal'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DetailJurnal::insert($rows);

        return $jurnal;
    }

    private function hitungTotalDebit(array $entries): float
    {
        return collect($entries)
            ->filter(fn($e) => strtoupper($e['tipe'] ?? '') === 'DEBIT')
            ->sum(fn($e) => (float) ($e['nominal'] ?? 0));
    }

    private function unggahBuktiTransaksi(Transaksi $transaksi, array $files): void
    {
        foreach ($files as $file) {
            if (!$file) continue;

            $path = $file->store("bukti-transaksi/{$transaksi->id}", 'public');

            BuktiTransaksi::create([
                'transaksi_id' => $transaksi->id,
                'nama_file'    => $file->getClientOriginalName(),
                'path_file'    => $path,
            ]);
        }
    }

    private function simpanAsetDariTransaksi(Transaksi $transaksi, array $data): void
    {
        $aset = $transaksi->aset()->create([
            'kode_aset'                => \App\Models\Aset::buatKode($data['tanggal_perolehan']),
            'nama_aset'                => $data['nama_aset'],
            'lokasi_aset'              => $data['lokasi_aset'],
            'kondisi_aset'             => match($data['kondisi_aset']) {
                'BAIK'         => 'BAIK',
                'RUSAK_RINGAN' => 'RUSAK RINGAN',
                'RUSAK_BERAT'  => 'RUSAK BERAT',
                default        => $data['kondisi_aset'],
            },
            'sumber_perolehan'         => $data['sumber_perolehan'],
            'tanggal_perolehan'        => $data['tanggal_perolehan'],
            'jumlah_unit'              => $data['jumlah_unit'] ?? 1,
            'nilai_tercatat'           => $data['jumlah'],
            'tanggal_mulai_penyusutan' => $data['tanggal_mulai_penyusutan'] ?? null,
            'umur_manfaat'             => $data['umur_manfaat'] ?? null,
            'keterangan'               => $data['keterangan_penyusutan'] ?? null,
            'status_aset'              => 'AKTIF',
            'nilai_buku'               => $data['jumlah'],
            'akumulasi_penyusutan'     => 0,
        ]);

        if (!empty($data['dokumen_aset']) && $data['dokumen_aset'] instanceof \Illuminate\Http\UploadedFile) {
            $path = $data['dokumen_aset']->store("aset-dokumen/{$aset->id}", 'public');
            $aset->update([
                'dokumen_path' => $path,
                'dokumen_nama' => $data['dokumen_aset']->getClientOriginalName(),
            ]);
        }
    }
}