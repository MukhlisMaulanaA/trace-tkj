<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('purchase_orders', function (Blueprint $table) {
      $table->id();

      $table->string('po_number')->unique();
      $table->date('po_date');

      // Relasi ke Project yang sudah ada
      $table->string('project_id')->nullable();

      $table->string('customer')->nullable();
      $table->string('location')->nullable();
      $table->string('quotation_no')->nullable();
      $table->string('pic')->nullable();

      $table->enum('status', [
        'draft',
        'submitted',
      ])->default('draft');

      $table->text('notes')->nullable();

      $table->decimal('subtotal', 15, 2)->default(0);

      $table->timestamps();

      $table->foreign('project_id')
        ->references('id')
        ->on('projects')
        ->nullOnDelete();

      $table->index('project_id');
      $table->index('status');
      $table->index('po_date');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('purchase_orders');
  }
};