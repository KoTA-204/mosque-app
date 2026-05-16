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
        Schema::create('akun', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_akun_id')
                ->constrained('kategori_akun')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('akun')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('kode_akun')->unique();
            $table->string('nama_akun');
            $table->enum('saldo_normal', [
                'DEBIT',
                'KREDIT'
            ]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akun');
    }
};
