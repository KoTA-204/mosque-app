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
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dompet_id')
                ->constrained('dompet')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('kegiatan_id')
                ->nullable()
                ->constrained('kegiatan')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            
            $table->foreignId('kategori_transaksi_id')
                ->constrained('kategori_transaksi')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->date('tanggal_transaksi');
            $table->decimal('jumlah', 15, 2);
            $table->text('deskripsi')->nullable();
            $table->enum('status_approval', [
                'PENDING',
                'APPROVED',
                'REJECTED',
                'REVISION'
            ])->default('PENDING');

            $table->enum('status_jurnal', [
                'UNMAPPED',
                'MAPPED'
            ])->default('UNMAPPED');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
