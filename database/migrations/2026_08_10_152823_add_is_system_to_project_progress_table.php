<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('project_progress', function (Blueprint $table) {
      $table->boolean('is_system')
        ->default(false)
        ->after('keterangan');
    });

    /*
     * Backfill project lama.
     *
     * Setiap project yang belum mempunyai event "Project Created"
     * akan mendapatkan satu system timeline event.
     */
    $projects = DB::table('projects')
      ->select('id', 'created_at')
      ->get();

    foreach ($projects as $project) {
      $exists = DB::table('project_progress')
        ->where('project_id', $project->id)
        ->where('is_system', true)
        ->exists();

      if (!$exists) {
        DB::table('project_progress')->insert([
          'project_id' => $project->id,
          'waktu_progres' => $project->created_at,
          'persentase' => 0,
          'keterangan' => 'Project Created',
          'is_system' => true,
          'created_at' => $project->created_at,
          'updated_at' => $project->created_at,
        ]);
      }
    }
  }

  public function down(): void
  {
    /*
     * Hapus system event yang dibuat oleh migration ini
     * sebelum menghapus kolomnya.
     */
    DB::table('project_progress')
      ->where('is_system', true)
      ->delete();

    Schema::table('project_progress', function (Blueprint $table) {
      $table->dropColumn('is_system');
    });
  }
};