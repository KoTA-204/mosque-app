<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kategori_transaksi', function (Blueprint $table) {
            $table->string('status')->default('aktif')->after('deskripsi');
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("
                ALTER TABLE kategori_transaksi
                ADD CONSTRAINT kategori_transaksi_status_check
                CHECK (status IN ('aktif', 'tidak_aktif'))
            ");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE kategori_transaksi DROP CONSTRAINT IF EXISTS kategori_transaksi_status_check');
        }

        Schema::table('kategori_transaksi', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};