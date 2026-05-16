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
        Schema::create('kencleng', function (Blueprint $table) {
            $table->id();

            $table->foreignId('transaksi_id')
                ->unique()
                ->constrained('transaksi')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->string('nomor_kwitansi')->nullable();
            $table->string('berita_acara');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kencleng');
    }
};
