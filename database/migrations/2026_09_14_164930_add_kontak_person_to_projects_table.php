<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('projects', function (Blueprint $table) {
      $table->string('kontak_person')
        ->nullable()
        ->after('kustomer');
    });
  }

  public function down(): void
  {
    Schema::table('projects', function (Blueprint $table) {
      $table->dropColumn('kontak_person');
    });
  }
};