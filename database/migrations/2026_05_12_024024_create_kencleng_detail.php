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
        Schema::create('kencleng_detail', function (Blueprint $table) {
            $table->id();

            $table->foreignId('kencleng_id')
                ->constrained('kencleng')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->integer('pecahan');
            $table->integer('jumlah_pecahan');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kencleng_detail');
    }
};
