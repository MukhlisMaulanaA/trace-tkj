<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    DB::table('invoices')
      ->select(['id', 'payment_progress'])
      ->whereNotNull('payment_progress')
      ->orderBy('id')
      ->chunkById(100, function ($invoices): void {
        foreach ($invoices as $invoice) {
          preg_match('/\d+(?:[.,]\d+)?/', (string) $invoice->payment_progress, $matches);
          $paymentProgress = isset($matches[0])
            ? max(0, min(100, (float) str_replace(',', '.', $matches[0])))
            : null;

          DB::table('invoices')
            ->where('id', $invoice->id)
            ->update(['payment_progress' => $paymentProgress]);
        }
      });

    Schema::table('invoices', function (Blueprint $table): void {
      $table->decimal('payment_progress', 5, 2)->nullable()->change();
    });
  }

  public function down(): void
  {
    Schema::table('invoices', function (Blueprint $table): void {
      $table->string('payment_progress')->nullable()->change();
    });
  }
};