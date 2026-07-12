<?php

namespace App\Http\Requests;

/**
 * Aturan update sama dengan store, jadi cukup mewarisinya.
 * Bila kelak perlu aturan berbeda saat edit, override rules() di sini.
 */
class UpdateJurnalPembukaRequest extends StoreJurnalPembukaRequest
{
    //
}
