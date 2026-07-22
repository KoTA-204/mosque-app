<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom pendukung fitur "kelola kredensial pengguna":
     * - password_awal: password awal yang DIBUAT ADMIN, disimpan TERENKRIPSI
     *   (reversible via cast 'encrypted') supaya admin tetap bisa melihatnya.
     *   Ini BUKAN kolom login; autentikasi tetap memakai kolom `password` (hash).
     * - kredensial_dikirim_pada: penanda kapan kredensial dikirim via email ke pengguna.
     *     NULL   => belum dikirim  (ikon email KUNING)
     *     terisi => sudah dikirim   (ikon email ABU-ABU)
     */
    public function up(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->text('password_awal')->nullable()->after('password');
            $table->timestamp('kredensial_dikirim_pada')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->dropColumn(['password_awal', 'kredensial_dikirim_pada']);
        });
    }
};
