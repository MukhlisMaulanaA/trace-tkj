<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('purchase_orders', function (Blueprint $table) {
      $table->boolean('dpp_enabled')
        ->default(false)
        ->after('discount_amount');

      $table->decimal('dpp_amount', 15, 2)
        ->default(0)
        ->after('dpp_enabled');
    });
  }

  public function down(): void
  {
    Schema::table('purchase_orders', function (Blueprint $table) {
      $table->dropColumn([
        'dpp_enabled',
        'dpp_amount',
      ]);
    });
  }
};