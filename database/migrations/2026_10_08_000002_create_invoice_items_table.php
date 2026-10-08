<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('invoice_items', function (Blueprint $table) {
      $table->id();
      $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
      $table->unsignedInteger('item_no');
      $table->text('description');
      $table->decimal('quantity', 15, 2)->default(1);
      $table->decimal('unit_price', 15, 2)->default(0);
      $table->decimal('amount', 15, 2)->default(0);
      $table->timestamps();

      $table->unique(['invoice_id', 'item_no']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('invoice_items');
  }
};