<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jurnal', function (Blueprint $table) {
            $table->enum('tipe_penutupan', [
                'TUTUP_PENDAPATAN',
                'TUTUP_BEBAN',
            ])->nullable()->after('tipe_penyesuaian');
        });
    }

    public function down(): void
    {
        Schema::table('jurnal', function (Blueprint $table) {
            $table->dropColumn('tipe_penutupan');
        });
    }
};