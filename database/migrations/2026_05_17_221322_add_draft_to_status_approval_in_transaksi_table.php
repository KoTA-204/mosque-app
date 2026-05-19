<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE transaksi DROP CONSTRAINT transaksi_status_approval_check");
            DB::statement("ALTER TABLE transaksi ADD CONSTRAINT transaksi_status_approval_check
                CHECK (status_approval IN ('DRAFT', 'PENDING', 'APPROVED', 'REJECTED', 'REVISION'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE transaksi DROP CONSTRAINT transaksi_status_approval_check");
            DB::statement("ALTER TABLE transaksi ADD CONSTRAINT transaksi_status_approval_check
                CHECK (status_approval IN ('PENDING', 'APPROVED', 'REJECTED', 'REVISION'))");
        }
    }
};