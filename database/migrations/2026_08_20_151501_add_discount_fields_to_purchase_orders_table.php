<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    if (!Schema::hasColumn('purchase_orders', 'discount_enabled')) {
      Schema::table('purchase_orders', function (Blueprint $table) {
        $table->boolean('discount_enabled')
          ->default(false)
          ->after('ppn_amount');
      });
    }

    if (!Schema::hasColumn('purchase_orders', 'discount_percent')) {
      Schema::table('purchase_orders', function (Blueprint $table) {
        $table->decimal('discount_percent', 5, 2)
          ->default(0)
          ->after('discount_enabled');
      });
    }

    if (!Schema::hasColumn('purchase_orders', 'discount_amount')) {
      Schema::table('purchase_orders', function (Blueprint $table) {
        $table->decimal('discount_amount', 15, 2)
          ->default(0)
          ->after('discount_percent');
      });
    }

    // grand_total sudah ada pada database,
    // jadi jangan dibuat ulang.
  }

  public function down(): void
  {
    if (Schema::hasColumn('purchase_orders', 'discount_amount')) {
      Schema::table('purchase_orders', function (Blueprint $table) {
        $table->dropColumn('discount_amount');
      });
    }

    if (Schema::hasColumn('purchase_orders', 'discount_percent')) {
      Schema::table('purchase_orders', function (Blueprint $table) {
        $table->dropColumn('discount_percent');
      });
    }

    if (Schema::hasColumn('purchase_orders', 'discount_enabled')) {
      Schema::table('purchase_orders', function (Blueprint $table) {
        $table->dropColumn('discount_enabled');
      });
    }
  }
};