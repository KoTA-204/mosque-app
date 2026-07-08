<?php

namespace App\Http\Requests;

use App\Services\Akuntansi\JurnalPenyesuaianService;
use Illuminate\Foundation\Http\FormRequest;

class StoreJurnalPenyesuaianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tipeKeys = implode(',', array_keys(JurnalPenyesuaianService::TIPE_LABELS));

        $rules = [
            'periode_id'       => 'required|exists:periode,id',
            'tanggal'          => 'required|date',
            'tipe_penyesuaian' => 'required|in:' . $tipeKeys,
            'keterangan'       => 'required|string|max:500',
            'detail'           => 'required|array|min:2',
            'detail.*.akun_id' => 'required|exists:akun,id',
            'detail.*.tipe'    => 'required|in:DEBIT,KREDIT',
            'detail.*.nominal' => 'required|string',
            'submit_type'      => 'required|in:draft,posting',
        ];

        // Aturan tambahan khusus penyusutan aset.
        if ($this->input('tipe_penyesuaian') === 'PENYUSUTAN_ASET') {
            $rules['detail.0.aset_rows']            = 'required|array|min:1';
            $rules['detail.0.aset_rows.*.aset_id']  = 'required|exists:aset,id';
            $rules['detail.0.aset_rows.*.nominal']  = 'required|string';
        }

        // Aturan tambahan khusus pelepasan aset.
        if ($this->input('tipe_penyesuaian') === 'PELEPASAN_ASET') {
            $rules['aset_dilepas']   = 'required|array|min:1';
            $rules['aset_dilepas.*'] = 'exists:aset,id';
        }

        return $rules;
    }
}
