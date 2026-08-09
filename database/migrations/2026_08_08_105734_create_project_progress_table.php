<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('project_progress', function (Blueprint $table) {
      $table->id();
      $table->string('project_id'); // Foreign key ke ID Project (string)
      $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');

      $table->dateTime('waktu_progres');
      $table->integer('persentase'); // 0 - 100
      $table->text('keterangan');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('project_progress');
  }
};
