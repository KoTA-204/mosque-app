<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom jenis_pelepasan ke tabel aset.
     * - jenis_pelepasan : DIJUAL | DIBUANG | DONASI | HILANG
     *   Hanya diisi saat alasan_nonaktif = AKAN_DILEPAS.
     */
    public function up(): void
    {
        Schema::table('aset', function (Blueprint $table) {
            $table->string('jenis_pelepasan', 20)->nullable()->after('tanggal_nonaktif');
        });
    }

    public function down(): void
    {
        Schema::table('aset', function (Blueprint $table) {
            $table->dropColumn('jenis_pelepasan');
        });
    }
};
