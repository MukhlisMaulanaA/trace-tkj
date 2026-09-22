<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('project_summaries', function (Blueprint $table) {
      $table->id();
      $table->string('project_id')->unique();
      $table->string('profit_mode')->default('percentage');
      $table->decimal('profit_percentage', 5, 2)->default(0);
      $table->decimal('nominal_profit', 15, 2)->default(0);
      $table->timestamps();

      $table->foreign('project_id')
        ->references('id')
        ->on('projects')
        ->cascadeOnDelete();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('project_summaries');
  }
};