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
        Schema::create('dompet', function (Blueprint $table) {
            $table->id();
            $table->string('nama_dompet');
            $table->enum('jenis_dompet', [
                'CASH',
                'BANK',
            ]);
            $table->string('nomor_rekening')->nullable();
            $table->string('nama_bank')->nullable();
            $table->decimal('saldo_awal', 15, 2)->default(0);   
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dompet');
    }
};
