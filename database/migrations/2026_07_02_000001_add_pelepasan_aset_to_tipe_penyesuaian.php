<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Menambahkan nilai 'PELEPASAN_ASET' pada enum kolom jurnal.tipe_penyesuaian.
 *
 * Di PostgreSQL, Laravel enum() diimplementasikan sebagai CHECK constraint,
 * sehingga penambahan nilai dilakukan dengan mengganti constraint tersebut.
 * Driver lain (mis. sqlite) tidak memaksa enum lewat CHECK sehingga dilewati.
 */
return new class extends Migration
{
    private array $lama = [
        'PENYUSUTAN_ASET', 'BEBAN_BELUM_DIBAYAR', 'PENDAPATAN_BELUM_DICATAT',
        'BEBAN_DIBAYAR_DIMUKA', 'ZAKAT_INFAQ', 'MANUAL',
    ];

    private array $baru = [
        'PENYUSUTAN_ASET', 'BEBAN_BELUM_DIBAYAR', 'PENDAPATAN_BELUM_DICATAT',
        'BEBAN_DIBAYAR_DIMUKA', 'ZAKAT_INFAQ', 'MANUAL', 'PELEPASAN_ASET',
    ];

    public function up(): void
    {
        $this->setEnum($this->baru);
    }

    public function down(): void
    {
        $this->setEnum($this->lama);
    }

    private function setEnum(array $values): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $list = collect($values)->map(fn ($v) => "'{$v}'")->implode(', ');

        DB::statement('ALTER TABLE jurnal DROP CONSTRAINT IF EXISTS jurnal_tipe_penyesuaian_check');
        DB::statement(
            "ALTER TABLE jurnal ADD CONSTRAINT jurnal_tipe_penyesuaian_check "
            . "CHECK (tipe_penyesuaian::text = ANY (ARRAY[{$list}]::text[]))"
        );
    }
};
