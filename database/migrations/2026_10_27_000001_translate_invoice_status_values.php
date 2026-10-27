<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE invoices MODIFY status VARCHAR(50) NOT NULL DEFAULT 'Draft'");

        DB::table('invoices')->where('status', 'draft')->update(['status' => 'Draft']);
        DB::table('invoices')->where('status', 'paid')->update(['status' => 'Sudah dibayar']);
        DB::table('invoices')->where('status', 'issued')->update(['status' => 'Proses pembayaran']);
        DB::table('invoices')->where('status', 'cancelled')->update(['status' => 'Belum dibayar']);

        DB::statement("ALTER TABLE invoices MODIFY status ENUM('Draft', 'Sudah dibayar', 'Proses pembayaran', 'Belum dibayar') NOT NULL DEFAULT 'Draft'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE invoices MODIFY status VARCHAR(50) NOT NULL DEFAULT 'draft'");

        DB::table('invoices')->where('status', 'Draft')->update(['status' => 'draft']);
        DB::table('invoices')->where('status', 'Sudah dibayar')->update(['status' => 'paid']);
        DB::table('invoices')->where('status', 'Proses pembayaran')->update(['status' => 'issued']);
        DB::table('invoices')->where('status', 'Belum dibayar')->update(['status' => 'cancelled']);

        DB::statement("ALTER TABLE invoices MODIFY status ENUM('draft', 'issued', 'paid', 'cancelled') NOT NULL DEFAULT 'draft'");
    }
};
