<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kegiatan', function (Blueprint $table) {
            $table->id();

            $table->string('nama_kegiatan');
            $table->enum('jenis_kegiatan', [
                'QURBAN',
                'ZAKAT',
                'KAJIAN',
                'SOSIAL',
                'LAINNYA'
            ]);

            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->decimal('anggaran', 18, 2)->default(0);

            $table->enum('status', [
                'AKTIF',
                'DITUTUP',
            ])->default('AKTIF');

            $table->foreignId('panitia_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
                
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatan');
    }
};