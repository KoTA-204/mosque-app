<?php

namespace Database\Factories;

use App\Models\KategoriTransaksi;
use Illuminate\Database\Eloquent\Factories\Factory;

class KategoriTransaksiFactory extends Factory
{
    protected $model = KategoriTransaksi::class;

    public function definition(): array
    {
        return [
            'nama_kategori' => $this->faker->unique()->words(2, true),
            'deskripsi'     => $this->faker->sentence(),
        ];
    }
}