<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('project_expenditures', function (Blueprint $table) {
      $table->string('attachment')->nullable()->after('expenditure_date');
    });
  }

  public function down(): void
  {
    Schema::table('project_expenditures', function (Blueprint $table) {
      $table->dropColumn('attachment');
    });
  }
};