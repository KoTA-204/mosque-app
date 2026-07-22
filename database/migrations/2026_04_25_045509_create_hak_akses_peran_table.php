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
        Schema::create('hak_akses_peran', function (Blueprint $table) {
            $table->foreignId('hak_akses_id')
                ->constrained('hak_akses')
                ->onDelete('cascade');
            $table->foreignId('peran_id')
                ->constrained('peran')
                ->onDelete('cascade'); 
                
            $table->primary(['hak_akses_id', 'peran_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hak_akses_peran');
    }
};
