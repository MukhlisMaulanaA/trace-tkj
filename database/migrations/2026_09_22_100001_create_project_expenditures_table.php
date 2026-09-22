<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('project_expenditures', function (Blueprint $table) {
      $table->id();
      $table->foreignId('project_summary_id')
        ->constrained('project_summaries')
        ->cascadeOnDelete();
      $table->string('type');
      $table->text('description');
      $table->decimal('amount', 15, 2)->default(0);
      $table->date('expenditure_date');
      $table->timestamps();

      $table->index(['project_summary_id', 'type']);
      $table->index('expenditure_date');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('project_expenditures');
  }
};