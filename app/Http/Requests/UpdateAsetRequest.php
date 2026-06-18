<?php

namespace App\Http\Requests;

class UpdateAsetRequest extends StoreAsetRequest
{
    // otorisasi request
    public function authorize(): bool
    {
        return true;
    }
}