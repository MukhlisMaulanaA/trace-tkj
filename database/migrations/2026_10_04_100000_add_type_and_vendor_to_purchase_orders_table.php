<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('purchase_orders', function (Blueprint $table) {
      $table->enum('type', [
        'project',
        'vendor',
      ])->default('project')->after('po_date');
      $table->foreignId('vendor_id')->nullable()->after('project_id')
        ->constrained('vendors')->nullOnDelete();
      $table->index('type');
    });
  }

  public function down(): void
  {
    Schema::table('purchase_orders', function (Blueprint $table) {
      $table->dropForeign(['vendor_id']);
      $table->dropIndex(['type']);
      $table->dropColumn(['type', 'vendor_id']);
    });
  }
};