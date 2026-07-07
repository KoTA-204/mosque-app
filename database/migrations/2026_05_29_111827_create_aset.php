<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the
     *  migrations.
     */
    public function up(): void
    {
        Schema::create('aset', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')
                ->nullable()
                ->constrained('transaksi')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('kode_aset')->nullable();
            $table->string('nama_aset');
            $table->string('sumber_perolehan');
            $table->date('tanggal_perolehan');
            $table->decimal('nilai_tercatat', 15, 2);
            $table->integer('umur_manfaat')->nullable();
            $table->enum('kondisi_aset', ['BAIK', 'RUSAK RINGAN', 'RUSAK BERAT']);
            $table->string('lokasi_aset');
            $table->string('nama_pemberi')->nullable();
            $table->integer('jumlah_unit')->default(1);
            $table->string('dokumen_pendukung')->nullable();
            $table->date('tanggal_mulai_penyusutan')->nullable();
            $table->text('keterangan')->nullable();
            $table->enum('status_aset', ['DRAFT', 'AKTIF', 'TIDAK AKTIF'])->default('AKTIF');
            $table->decimal('nilai_buku', 15, 2)->default(0);
            $table->decimal('akumulasi_penyusutan', 15, 2)->nullable();
            $table->timestamps();
            $table->softDeletes(); // ← tambah ini
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aset');
    }
};
