<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE akun DROP CONSTRAINT IF EXISTS akun_saldo_normal_check');
        }

        Schema::table('akun', function (Blueprint $table) {
            $table->string('saldo_normal')->nullable()->change();
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("
                ALTER TABLE akun
                ADD CONSTRAINT akun_saldo_normal_check
                CHECK (saldo_normal IS NULL OR saldo_normal IN ('DEBIT', 'KREDIT', 'debit', 'kredit'))
            ");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE akun DROP CONSTRAINT IF EXISTS akun_saldo_normal_check');
        }

        DB::statement("UPDATE akun SET saldo_normal = 'debit' WHERE saldo_normal IS NULL");

        Schema::table('akun', function (Blueprint $table) {
            $table->string('saldo_normal')->nullable(false)->change();
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("
                ALTER TABLE akun
                ADD CONSTRAINT akun_saldo_normal_check
                CHECK (saldo_normal IN ('DEBIT', 'KREDIT', 'debit', 'kredit'))
            ");
        }
    }
};