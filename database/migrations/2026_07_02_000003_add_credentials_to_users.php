<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom pendukung fitur "kelola kredensial user":
     * - initial_password: password awal yang DIBUAT ADMIN, disimpan TERENKRIPSI
     *   (reversible via cast 'encrypted') supaya admin tetap bisa melihatnya.
     *   Ini BUKAN kolom login; autentikasi tetap memakai kolom `password` (hash).
     * - credentials_sent_at: penanda kapan kredensial dikirim via email ke user.
     *     NULL   => belum dikirim  (ikon email KUNING)
     *     terisi => sudah dikirim   (ikon email ABU-ABU)
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('initial_password')->nullable()->after('password');
            $table->timestamp('credentials_sent_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['initial_password', 'credentials_sent_at']);
        });
    }
};
