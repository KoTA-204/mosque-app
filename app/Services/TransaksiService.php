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
    public function store(StoreTransaksiRequest $request): Transaksi
    {
        return DB::transaction(function () use ($request) {

            // 1. Simpan transaksi
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

            // 2. Jurnal entri debit & kredit
            $this->buatJurnalUmum(
                $transaksi,
                $request->akun_debit_id,
                $request->akun_kredit_id,
                $request->jumlah,
                $request->deskripsi,
            );

            // 3. Upload bukti (bisa multi-file)
            $this->uploadBukti($transaksi, $request->file('bukti_transaksi') ?? []);

            // 4. Simpan data aset jika toggle aktif
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
                'dompet_id'             => $request->dompet_id,
                'tanggal_transaksi'     => $request->tanggal_transaksi,
                'jenis_transaksi'       => $request->jenis_transaksi,
                'jumlah'                => $request->jumlah,
                'deskripsi'             => $request->deskripsi,
                'catatan'               => $request->catatan,
            ]);

            // Update akun jurnal entri
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

            // Upload bukti baru (jika ada)
            $this->uploadBukti($transaksi, $request->file('bukti_transaksi') ?? []);

            return $transaksi->fresh(['buktiTransaksi', 'jurnal.detailJurnal.akun']);
        });
    }

    public function destroy(Transaksi $transaksi): void
    {
        DB::transaction(function () use ($transaksi) {
            // Hapus file dari storage
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
        $rowsMap      = collect($sessionData['rows'])->keyBy('no_referensi');
        $klasMap      = collect($klasifikasi)->keyBy('no_referensi');
        $jenis        = $sessionData['jenis_transaksi'];

        $tersimpan = 0;
        $dilewati  = 0;
        $duplikat  = 0;

        DB::transaction(function () use (
            $rowsMap, $klasMap, $jenis, &$tersimpan, &$dilewati, &$duplikat
        ) {
            foreach ($rowsMap as $ref => $row) {
                // Skip duplikat
                if ($row['is_duplikat']) {
                    $duplikat++;
                    continue;
                }

                $klas = $klasMap->get($ref);

                // Skip jika tidak ada klasifikasi atau ditandai skip
                if (!$klas) {
                    $dilewati++;
                    continue;
                }

                // Simpan transaksi
                $tanggal   = substr($row['waktu_transaksi'] ?? $row['tanggal'], 0, 10);
                $transaksi = Transaksi::create([
                    'dompet_id'             => $sessionData['dompet_id'], 
                    'user_id'               => Auth::id(),
                    'kategori_transaksi_id' => null,
                    'tanggal_transaksi'     => $tanggal,
                    'jenis_transaksi'       => $jenis,
                    'jumlah'                => $row['jumlah'],
                    'deskripsi'             => $row['deskripsi'],
                    'catatan'               => $row['no_referensi'],
                    'status_approval'       => null,
                    'status_jurnal'         => 'MAPPED',
                ]);

                // Simpan jurnal umum
                $this->upsertJurnalUmum(
                    $transaksi,
                    $klas['akun_debit_id'],
                    $klas['akun_kredit_id'],
                    $row['jumlah'],
                    $row['deskripsi'],
                );

                $tersimpan++;
            }
        });

        return [
            'tersimpan' => $tersimpan,
            'dilewati'  => $dilewati,
            'duplikat'  => $duplikat,
            'total'     => $rowsMap->count(),
        ];
    }

    private function buatJurnalUmum(
        Transaksi $transaksi,
        int $akunDebitId,
        int $akunKreditId,
        float $jumlah,
        ?string $deskripsi = null
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
                'jurnal_id' => $jurnal->id,
                'akun_id'   => $akunDebitId,
                'tipe'    => 'DEBIT',
                'nominal'   => $jumlah,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jurnal_id' => $jurnal->id,
                'akun_id'   => $akunKreditId,
                'tipe'    => 'KREDIT',
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
            'nama_aset'                => $data['nama_aset'],
            'lokasi_aset'              => $data['lokasi_aset'],
            'kondisi_aset'             => $data['kondisi_aset'],
            'sumber_perolehan'         => $data['sumber_perolehan'],
            'tanggal_perolehan'        => $data['tanggal_perolehan'],
            'jumlah_unit'              => $data['jumlah_unit'] ?? 1,
            'harga_perolehan'          => $data['jumlah'],
            'tanggal_mulai_penyusutan' => $data['tanggal_mulai_penyusutan'] ?? null,
            'umur_manfaat'             => $data['umur_manfaat'] ?? null,
            'keterangan_penyusutan'    => $data['keterangan_penyusutan'] ?? null,
            'user_id'                  => Auth::id(),
        ]);

        // Upload dokumen aset
        if (!empty($data['dokumen_aset']) && $data['dokumen_aset'] instanceof \Illuminate\Http\UploadedFile) {
            $path = $data['dokumen_aset']->store("aset-dokumen/{$aset->id}", 'public');
            $aset->update([
                'dokumen_path' => $path,
                'dokumen_nama' => $data['dokumen_aset']->getClientOriginalName(),
            ]);
        }
    }
}