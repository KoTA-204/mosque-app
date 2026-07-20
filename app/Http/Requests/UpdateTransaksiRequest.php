<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransaksiRequest extends FormRequest
{
    protected $errorBag = 'editTransaksi'; 
    
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dompet_id'             => 'required|exists:dompet,id',
            'tanggal_transaksi'     => 'required|date',
            'jenis_transaksi'       => 'required|in:PEMASUKAN,PENGELUARAN',
            'jurnal'                => 'required|array|min:2',
            'jurnal.*.akun_id'      => 'required|exists:akun,id',
            'jurnal.*.tipe'         => 'required|in:DEBIT,KREDIT',
            'jurnal.*.nominal'      => 'required|numeric|min:1',
            'deskripsi'             => 'nullable|string|max:500',
            'bukti_transaksi'       => 'required|array|max:5',
            'bukti_transaksi.*'     => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'dompet_id.required'             => 'Dompet wajib dipilih.',
            'tanggal_transaksi.required'     => 'Tanggal transaksi wajib diisi.',
            'jenis_transaksi.required'       => 'Jenis transaksi wajib dipilih',
            'akun_debit_id.required'         => 'Akun debit wajib dipilih.',
            'akun_kredit_id.required'        => 'Akun kredit wajib dipilih.',
            'bukti_transaksi.required'       => 'Bukti transaksi wajib diunggah.',
            'bukti_transaksi.*.mimes'        => 'File harus berformat JPG, PNG, atau PDF.',
            'bukti_transaksi.*.max'          => 'Ukuran file maksimal 5 MB.',
        ];
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
