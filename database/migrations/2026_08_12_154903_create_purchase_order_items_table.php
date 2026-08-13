<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('purchase_order_items', function (Blueprint $table) {
      $table->id();

      $table->foreignId('purchase_order_id')
        ->constrained('purchase_orders')
        ->cascadeOnDelete();

      // Nomor item otomatis dalam PO
      $table->unsignedInteger('item_no');

      // Optional section/group
      $table->string('section')->nullable();

      $table->text('description');

      $table->decimal('quantity', 15, 3);

      // Satuan bebas dari user
      $table->string('sat', 50);

      $table->decimal('unit_price', 15, 2)->default(0);

      // Keterangan tambahan
      $table->text('notes')->nullable();

      // quantity × unit_price
      $table->decimal('total_price', 15, 2)->default(0);

      $table->timestamps();

      $table->index([
        'purchase_order_id',
        'item_no',
      ]);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('purchase_order_items');
  }
};