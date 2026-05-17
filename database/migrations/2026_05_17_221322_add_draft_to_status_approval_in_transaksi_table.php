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
        DB::statement("ALTER TABLE transaksi DROP CONSTRAINT transaksi_status_approval_check");
        DB::statement("ALTER TABLE transaksi ADD CONSTRAINT transaksi_status_approval_check
            CHECK (status_approval IN ('DRAFT', 'PENDING', 'APPROVED', 'REJECTED', 'REVISION'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE transaksi DROP CONSTRAINT transaksi_status_approval_check");
        DB::statement("ALTER TABLE transaksi ADD CONSTRAINT transaksi_status_approval_check
            CHECK (status_approval IN ('PENDING', 'APPROVED', 'REJECTED', 'REVISION'))");
    }
};
