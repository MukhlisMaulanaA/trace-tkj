<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('projects', function (Blueprint $table) {
      // ID sebagai Primary Key berpola String (contoh: P26H001)
      $table->string('id')->primary();

      // Kolom Data Project
      $table->string('nama_project');
      $table->string('kustomer');
      $table->string('lokasi');
      $table->string('nomor_quotation');

      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('projects');
  }
};