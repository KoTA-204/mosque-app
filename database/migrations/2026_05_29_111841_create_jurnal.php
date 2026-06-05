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
        Schema::create('jurnal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')
                ->constrained('periode')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            
            $table->foreignId('transaksi_id')
                ->nullable()
                ->unique()
                ->constrained('transaksi')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('jurnal_ref_id')
                ->nullable()
                ->constrained('jurnal')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->date('tanggal');
            $table->enum('tipe_penyesuaian', [
                'PENYUSUTAN_ASET',
                'BEBAN_BELUM_DIBAYAR',
                'PENDAPATAN_BELUM_DICATAT',
                'BEBAN_DIBAYAR_DIMUKA',
                'ZAKAT_INFAQ',
                'MANUAL',
            ])->nullable();
            $table->enum('jenis_jurnal', ['PEMBUKA','UMUM', 'PENYESUAIAN', 'KOREKSI', 'PENUTUP']);
            $table->text('keterangan')->nullable();
            $table->enum('status', ['DRAFT', 'POSTED'])->default('DRAFT');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnal');
    }
};
