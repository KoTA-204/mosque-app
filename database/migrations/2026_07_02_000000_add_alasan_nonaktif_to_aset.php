<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan jejak alasan penonaktifan aset.
     * - alasan_nonaktif  : MENGANGGUR | RUSAK_BERAT | AKAN_DILEPAS
     * - catatan_nonaktif : catatan bebas bendahara (mis. rencana pelepasan)
     * - tanggal_nonaktif : tanggal aset dinonaktifkan (audit trail)
     */
    public function up(): void
    {
        Schema::table('aset', function (Blueprint $table) {
            $table->string('alasan_nonaktif', 30)->nullable()->after('status_aset');
            $table->text('catatan_nonaktif')->nullable()->after('alasan_nonaktif');
            $table->date('tanggal_nonaktif')->nullable()->after('catatan_nonaktif');
        });
    }

    public function down(): void
    {
        Schema::table('aset', function (Blueprint $table) {
            $table->dropColumn(['alasan_nonaktif', 'catatan_nonaktif', 'tanggal_nonaktif']);
        });
    }
};
