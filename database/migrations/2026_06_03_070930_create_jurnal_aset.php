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
        Schema::create('jurnal_aset', function (Blueprint $table) {
            $table->foreignId('aset_id')
                ->constrained('aset')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('jurnal_id')
                ->constrained('jurnal')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->decimal('nominal', 15, 2);
            $table->primary(['aset_id', 'jurnal_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnal_aset');
    }
};
