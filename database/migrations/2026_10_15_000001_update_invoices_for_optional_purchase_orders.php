<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('invoices', function (Blueprint $table): void {
      $table->foreignId('purchase_order_id')->nullable()->change();
      $table->string('payment_type')->default('full_payment')->change();
    });
  }

  public function down(): void
  {
    Schema::table('invoices', function (Blueprint $table): void {
      $table->enum('payment_type', ['full_payment', 'down_payment'])->default('full_payment')->change();
      $table->foreignId('purchase_order_id')->nullable(false)->change();
    });
  }
};