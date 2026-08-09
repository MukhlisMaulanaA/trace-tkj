<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectProgress extends Model
{
  use HasFactory;

  protected $fillable = [
    'project_id',
    'waktu_progres',
    'persentase',
    'keterangan',
  ];

  protected function casts(): array
  {
    return [
      'waktu_progres' => 'datetime',
    ];
  }

  public function project(): BelongsTo
  {
    return $this->belongsTo(Project::class);
  }
}
