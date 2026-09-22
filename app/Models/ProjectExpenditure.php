<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectExpenditure extends Model
{
  use HasFactory;

  public const TYPE_MATERIAL = 'material';
  public const TYPE_LABOUR = 'labour';

  protected $fillable = [
    'project_summary_id',
    'type',
    'description',
    'amount',
    'expenditure_date',
  ];

  protected function casts(): array
  {
    return [
      'amount' => 'decimal:0',
      'expenditure_date' => 'date',
    ];
  }

  public function projectSummary(): BelongsTo
  {
    return $this->belongsTo(ProjectSummary::class);
  }
}