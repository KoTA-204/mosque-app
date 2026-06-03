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
        Schema::create('detail_jurnal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jurnal_id')
                ->constrained('jurnal')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('akun_id')
                ->constrained('akun')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->enum('tipe', ['DEBIT', 'KREDIT']);
            $table->decimal('nominal', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_jurnal');
    }
};
