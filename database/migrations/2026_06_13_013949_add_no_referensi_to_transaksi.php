<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            Schema::table('transaksi', function (Blueprint $table) {
                $table->string('no_referensi')->nullable()->after('deskripsi');
            });
        });

        DB::statement('
            CREATE UNIQUE INDEX unique_transaksi_no_referensi
            ON transaksi (no_referensi)
            WHERE no_referensi IS NOT NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS unique_transaksi_no_referensi');
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropColumn('no_referensi');
        });
    }
};
