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
                'DRAFT', 
                'BERJALAN', 
                'SELESAI',
                'DIBATALKAN'
            ])->default('DRAFT');

            $table->foreignId('panitia_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
                
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatan');
    }
};
