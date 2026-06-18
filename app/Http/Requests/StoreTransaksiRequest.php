<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransaksiRequest extends FormRequest
{
    protected $errorBag = 'createTransaksi';

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // otorisasi detail ditangani di controller
    }

    public function rules(): array
        {
            $rules = [
            'jenis_transaksi'       => 'required|in:PEMASUKAN,PENGELUARAN',
            'tanggal_transaksi'     => 'required|date',
            'kategori_transaksi'    => 'nullable|exists:kategori_transaksi_id',
            'dompet_id'             => 'required|exists:dompet,id',
            'jurnal'                => 'required|array|min:2',
            'jurnal.*.akun_id'      => 'required|exists:akun,id',
            'jurnal.*.tipe'         => 'required|in:DEBIT,KREDIT',
            'jurnal.*.nominal'      => 'required|numeric|min:1',
            'deskripsi'             => 'nullable|string|max:500',
            'catatan'               => 'nullable|string|max:500',
            'bukti_transaksi'       => 'nullable|array',
            'bukti_transaksi.*'     => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
            'is_aset'               => 'nullable|boolean',
            'force'                 => 'nullable|boolean',  
        ];

        if ($this->boolean('is_aset')) {
            $rules = array_merge($rules, [
                'nama_aset'                => 'required|string|max:200',
                'tanggal_perolehan'        => 'required|date',
                'kondisi_aset'             => 'required|in:BARU,BAIK,RUSAK_RINGAN,RUSAK_BERAT',
                'sumber_perolehan'         => 'required|in:PEMBELIAN,DONASI,WAKAF,HIBAH',
                'lokasi_aset'              => 'required|string|max:200',
                'jumlah_unit'              => 'required|integer|min:1',
                'dokumen_aset'             => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'tanggal_mulai_penyusutan' => 'required|date',
                'umur_manfaat'             => 'required|integer|min:1',
                'keterangan_penyusutan'    => 'nullable|string|max:500',
            ]);
        }

        return $rules;
    }
    
    public function messages(): array
    {
        return [
            'jenis_transaksi.required'       => 'Jenis transaksi wajib dipilih',
            'tanggal_transaksi.required'     => 'Tanggal wajib diisi',
            'akun_debit_id.required'         => 'Akun debit wajib dipilih.',
            'akun_kredit_id.required'        => 'Akun kredit wajib dipilih.',
            'dompet_id.required'             => 'Dompet wajib dipilih',
            'bukti_transaksi.*.mimes'        => 'File harus berformat JPG, PNG, atau PDF',
            'bukti_transaksi.*.max'          => 'Ukuran file maksimal 5MB',
            'catatan.max'                    => 'Catatan tidak boleh lebih dari 500 karakter',
            'nama_aset.required'             => 'Nama aset wajib diisi.',
            'tanggal_perolehan.required'     => 'Tanggal perolehan aset wajib diisi.',
            'kondisi_aset.required'          => 'Kondisi aset wajib dipilih.',
            'sumber_perolehan.required'      => 'Sumber perolehan aset wajib dipilih.',
            'lokasi_aset.required'           => 'Lokasi aset wajib diisi.',
            'jumlah_unit.required'           => 'Jumlah unit aset wajib diisi.',
            'tanggal_mulai_penyusutan.required' => 'Tanggal mulai penyusutan wajib diisi.',
            'umur_manfaat.required'          => 'Umur manfaat aset wajib diisi.',
            'dokumen_aset.max'               => 'Ukuran file dokumen aset maksimal 5MB',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_aset')) {
            $this->merge([
                'is_aset' => filter_var($this->is_aset, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $entries = $this->input('jurnal', []);

            $totalDebit  = 0;
            $totalKredit = 0;

            foreach ($entries as $e) {
                $tipe    = strtoupper($e['tipe'] ?? '');
                $nominal = (float) ($e['nominal'] ?? 0);

                if ($tipe === 'DEBIT')  $totalDebit  += $nominal;
                if ($tipe === 'KREDIT') $totalKredit += $nominal;
            }

            if (abs($totalDebit - $totalKredit) > 0.5) {
                $validator->errors()->add(
                    'jurnal',
                    'Total debit (Rp' . number_format($totalDebit, 0, ',', '.') .
                    ') harus sama dengan total kredit (Rp' . number_format($totalKredit, 0, ',', '.') . ').'
                );
            }
        });
    }
}
