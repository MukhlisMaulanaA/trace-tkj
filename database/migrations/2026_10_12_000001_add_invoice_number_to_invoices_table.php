<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('invoices', function (Blueprint $table): void {
      $table->string('invoice_number')->nullable()->unique()->after('invoice_code');
    });

    DB::table('invoices')->whereNull('invoice_number')->update([
      'invoice_number' => DB::raw('invoice_code'),
    ]);
  }

  public function down(): void
  {
    Schema::table('invoices', function (Blueprint $table): void {
      $table->dropUnique(['invoice_number']);
      $table->dropColumn('invoice_number');
    });
  }
};