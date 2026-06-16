<?php
namespace App\Services;

use App\Models\Transaksi;
use App\Models\BuktiTransaksi;
use App\Models\Jurnal;
use App\Models\DetailJurnal;
use App\Http\Requests\StoreTransaksiRequest;
use App\Http\Requests\UpdateTransaksiRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TransaksiService
{
    public function store(StoreTransaksiRequest $request, bool $force = false): Transaksi
    {
        // Cek duplikat SEBELUM transaction agar read tidak terpengaruh nested savepoint
        if (!$force) {
            $duplikat = Transaksi::with(['kategoriTransaksi', 'dompet'])
                ->whereDate('tanggal_transaksi', $request->tanggal_transaksi)
                ->where('jumlah', number_format((float)$request->jumlah, 2, '.', ''))
                ->where('jenis_transaksi', $request->jenis_transaksi)
                ->where('dompet_id', $request->dompet_id)
                ->first();

            if ($duplikat) {
                throw new \RuntimeException(
                    'DUPLIKAT_WARNING:' . json_encode([
                        'tanggal'  => $duplikat->tanggal_transaksi->format('d M Y'),
                        'jumlah'   => number_format($duplikat->jumlah, 0, ',', '.'),
                        'jenis'    => $duplikat->jenis_transaksi,
                        'deskripsi'=> $duplikat->deskripsi ?? '-',
                        'kategori' => $duplikat->kategoriTransaksi?->nama_kategori ?? '-',
                        'dompet'   => $duplikat->dompet?->nama_dompet ?? '-',
                    ])
                );
            }
        }

        return DB::transaction(function () use ($request) {
            $transaksi = Transaksi::create([
                'dompet_id'             => $request->dompet_id,
                'kegiatan_id'           => null,
                'user_id'               => Auth::id(),
                'kategori_transaksi_id' => $request->kategori_transaksi_id,
                'tanggal_transaksi'     => $request->tanggal_transaksi,
                'jenis_transaksi'       => $request->jenis_transaksi,
                'jumlah'                => $request->jumlah,
                'deskripsi'             => $request->deskripsi,
                'catatan'               => $request->catatan,
                'status_approval'       => null,
                'status_jurnal'         => 'MAPPED',
            ]);

            $this->buatJurnalUmum(
                $transaksi,
                $request->akun_debit_id,
                $request->akun_kredit_id,
                $request->jumlah,
                $request->deskripsi,
            );

            $this->uploadBukti($transaksi, $request->file('bukti_transaksi') ?? []);

            if ($request->boolean('is_aset')) {
                $this->simpanAset($transaksi, $request->all());
            }

            return $transaksi->load('buktiTransaksi', 'jurnal.detailJurnal.akun', 'aset');
        });
    }

    public function update(UpdateTransaksiRequest $request, Transaksi $transaksi): Transaksi
    {
        return DB::transaction(function () use ($request, $transaksi) {
            $transaksi->update([
                'dompet_id'         => $request->dompet_id,
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'jenis_transaksi'   => $request->jenis_transaksi,
                'jumlah'            => $request->jumlah,
                'deskripsi'         => $request->deskripsi,
                'catatan'           => $request->catatan,
            ]);

            $jurnalLama = $transaksi->jurnal()->where('jenis_jurnal', 'UMUM')->first();
            if ($jurnalLama) {
                $jurnalLama->detailJurnal()->delete();
                $jurnalLama->delete();
            }

            $this->buatJurnalUmum(
                $transaksi,
                $request->akun_debit_id,
                $request->akun_kredit_id,
                $request->jumlah,
                $request->deskripsi,
            );

            $this->uploadBukti($transaksi, $request->file('bukti_transaksi') ?? []);

            return $transaksi->fresh(['buktiTransaksi', 'jurnal.detailJurnal.akun']);
        });
    }

    public function destroy(Transaksi $transaksi): void
    {
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

    public function simpanImport(array $sessionData, array $klasifikasi): array
    {
        $rowsMap = collect($sessionData['rows'])->keyBy('no_referensi');
        $klasMap = collect($klasifikasi)->keyBy('no_referensi');
        $jenis   = $sessionData['jenis_transaksi'];

        $tersimpan    = 0;
        $dilewati     = 0;
        $duplikat     = 0;
        $gagalPeriode = [];

        foreach ($rowsMap as $ref => $row) {
            if ($row['is_duplikat']) {
                $duplikat++;
                continue;
            }

            $klas = $klasMap->get($ref);
            if (!$klas) {
                $dilewati++;
                continue;
            }

            $sudahAda = Transaksi::where('no_referensi', $row['no_referensi'])->exists();
            if ($sudahAda) {
                $duplikat++;
                continue;
            }

            $tanggal = substr($row['waktu_transaksi'] ?? now()->toDateString(), 0, 10);

            $periode = \App\Models\Periode::aktif()
                ->where('tanggal_awal', '<=', $tanggal)
                ->where('tanggal_akhir', '>=', $tanggal)
                ->first();

            if (!$periode) {
                $dilewati++;
                $gagalPeriode[$tanggal] = ($gagalPeriode[$tanggal] ?? 0) + 1;
                continue;
            }

            DB::transaction(function () use ($row, $klas, $jenis, $tanggal, $sessionData, $periode) {
                $transaksi = Transaksi::create([
                    'dompet_id'             => $sessionData['dompet_id'],
                    'user_id'               => Auth::id(),
                    'kategori_transaksi_id' => null,
                    'tanggal_transaksi'     => $tanggal,
                    'jenis_transaksi'       => $jenis,
                    'jumlah'                => $row['jumlah'],
                    'deskripsi'             => $row['deskripsi'],
                    'no_referensi'          => $row['no_referensi'],
                    'catatan'               => null,
                    'status_approval'       => null,
                    'status_jurnal'         => 'MAPPED',
                ]);

                $this->buatJurnalUmum(
                    $transaksi,
                    $klas['akun_debit_id'],
                    $klas['akun_kredit_id'],
                    $row['jumlah'],
                    $row['deskripsi'],
                    $periode,
                );
            });

            $tersimpan++;
        }

        return [
            'tersimpan'    => $tersimpan,
            'dilewati'     => $dilewati,
            'duplikat'     => $duplikat,
            'total'        => $rowsMap->count(),
            'gagalPeriode' => $gagalPeriode,
        ];
    }

    private function buatJurnalUmum(
        Transaksi $transaksi,
        int $akunDebitId,
        int $akunKreditId,
        float $jumlah,
        ?string $deskripsi = null,
        ?\App\Models\Periode $periode = null
    ): Jurnal {
        $periode = \App\Models\Periode::aktif()
            ->where('tanggal_awal', '<=', $transaksi->tanggal_transaksi)
            ->where('tanggal_akhir', '>=', $transaksi->tanggal_transaksi)
            ->first();

        if (!$periode) {
            throw new \RuntimeException(
                'Tidak ada periode aktif untuk tanggal ' . $transaksi->tanggal_transaksi
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

        DetailJurnal::insert([
            [
                'jurnal_id'  => $jurnal->id,
                'akun_id'    => $akunDebitId,
                'tipe'       => 'DEBIT',
                'nominal'    => $jumlah,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jurnal_id'  => $jurnal->id,
                'akun_id'    => $akunKreditId,
                'tipe'       => 'KREDIT',
                'nominal'    => $jumlah,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        return $jurnal;
    }

    private function uploadBukti(Transaksi $transaksi, array $files): void
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

    private function simpanAset(Transaksi $transaksi, array $data): void
    {
        $aset = $transaksi->aset()->create([
            'kode_aset'                => \App\Models\Aset::generateKode($data['tanggal_perolehan']),
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