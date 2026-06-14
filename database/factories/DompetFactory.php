<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DompetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama_dompet'    => $this->faker->words(2, true),
            'jenis_dompet'   => $this->faker->randomElement(['CASH', 'BANK']),
            'nomor_rekening' => null,
            'nama_bank'      => null,
            'saldo_awal'     => 0,
            // TIDAK ada user_id — kolom tidak ada di tabel dompet
        ];
    }
}