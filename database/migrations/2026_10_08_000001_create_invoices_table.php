<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('invoices', function (Blueprint $table) {
      $table->id();
      $table->string('invoice_code')->unique();
      $table->string('recipient');
      $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
      $table->string('project_name')->nullable();
      $table->date('invoice_date');
      $table->enum('payment_type', ['full_payment', 'down_payment'])->default('full_payment');
      $table->string('payment_progress')->nullable();
      $table->decimal('payment_progress_amount', 15, 2)->nullable();
      $table->text('payment_notes')->nullable();
      $table->string('payment_method');
      $table->enum('status', ['draft', 'issued', 'paid', 'cancelled'])->default('draft');
      $table->decimal('subtotal', 15, 2)->default(0);
      $table->boolean('discount_enabled')->default(false);
      $table->string('discount_type')->default('percent');
      $table->decimal('discount_percent', 8, 2)->default(0);
      $table->decimal('discount_amount', 15, 2)->default(0);
      $table->boolean('dpp_enabled')->default(false);
      $table->decimal('dpp_amount', 15, 2)->default(0);
      $table->boolean('ppn_enabled')->default(false);
      $table->decimal('ppn_amount', 15, 2)->default(0);
      $table->decimal('grand_total', 15, 2)->default(0);
      $table->timestamps();

      $table->index(['purchase_order_id', 'invoice_date']);
      $table->index('status');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('invoices');
  }
};