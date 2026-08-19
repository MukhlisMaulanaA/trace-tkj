<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('purchase_orders', function (Blueprint $table) {
      $table->string('po_code', 50)
        ->nullable()
        ->unique()
        ->after('id');

      $table->boolean('ppn_enabled')
        ->default(false)
        ->after('subtotal');

      $table->decimal('ppn_amount', 15, 0)
        ->default(0)
        ->after('ppn_enabled');

      $table->decimal('grand_total', 15, 0)
        ->default(0)
        ->after('ppn_amount');
    });
  }

  public function down(): void
  {
    Schema::table('purchase_orders', function (Blueprint $table) {
      $table->dropUnique([
        'po_code',
      ]);

      $table->dropColumn([
        'po_code',
        'ppn_enabled',
        'ppn_amount',
        'grand_total',
      ]);
    });
  }
};