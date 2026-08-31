<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('purchase_order_progress', function (Blueprint $table) {
      $table->id();

      // Foreign key to purchase_orders
      $table->foreignId('purchase_order_id')
        ->constrained('purchase_orders')
        ->cascadeOnDelete();

      // Invoice details
      $table->string('title'); // Invoice title/number
      $table->dateTime('invoice_date'); // Date of the invoice
      $table->string('pdf_file')->nullable(); // Path to PDF file
      $table->decimal('amount', 15, 2); // Invoice amount/value

      // Progress calculation
      $table->decimal('percentage', 5, 2)->default(0); // Auto-calculated percentage (0-100)

      // System flag
      $table->boolean('is_system')->default(false);

      $table->timestamps();

      $table->index('purchase_order_id');
      $table->index('invoice_date');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('purchase_order_progresses');
  }
};
