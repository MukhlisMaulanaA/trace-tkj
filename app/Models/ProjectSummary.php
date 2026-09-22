<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectSummary extends Model
{
  use HasFactory;

  public const PROFIT_MODE_PERCENTAGE = 'percentage';
  public const PROFIT_MODE_NOMINAL = 'nominal';
  public const PPH_RATE = 0.0265;

  protected $fillable = [
    'project_id',
    'profit_mode',
    'profit_percentage',
    'nominal_profit',
  ];

  protected function casts(): array
  {
    return [
      'profit_percentage' => 'decimal:2',
      'nominal_profit' => 'decimal:0',
    ];
  }

  public function project(): BelongsTo
  {
    return $this->belongsTo(Project::class);
  }

  public function expenditures(): HasMany
  {
    return $this->hasMany(ProjectExpenditure::class);
  }

  public function getOwnerCustomerAttribute(): ?string
  {
    return $this->project?->kustomer;
  }

  public function getProjectNameAttribute(): ?string
  {
    return $this->project?->nama_project;
  }

  public function getPoNumbersAttribute(): string
  {
    return $this->project?->purchaseOrders()
      ->pluck('po_number')
      ->filter()
      ->implode(', ') ?? '';
  }

  public function getContractValueAttribute(): float
  {
    return round((float) $this->project?->purchaseOrders()->sum('grand_total'), 0);
  }

  public function getPphAmountAttribute(): float
  {
    return round($this->contract_value * self::PPH_RATE, 0);
  }

  public function getFinalContractValueAttribute(): float
  {
    return $this->contract_value + $this->pph_amount;
  }

  public function getMaterialExpenditureAttribute(): float
  {
    return round((float) $this->expenditures()->where('type', ProjectExpenditure::TYPE_MATERIAL)->sum('amount'), 0);
  }

  public function getLabourExpenditureAttribute(): float
  {
    return round((float) $this->expenditures()->where('type', ProjectExpenditure::TYPE_LABOUR)->sum('amount'), 0);
  }

  public function getMlExpenditureAttribute(): float
  {
    return $this->material_expenditure + $this->labour_expenditure;
  }

  public function getFinalProfitAttribute(): float
  {
    if ($this->profit_mode === self::PROFIT_MODE_NOMINAL) {
      return round((float) $this->nominal_profit, 0);
    }

    return round($this->final_contract_value * ((float) $this->profit_percentage / 100), 0);
  }

  public function getRemainingBudgetAttribute(): float
  {
    return $this->final_contract_value - $this->ml_expenditure - $this->final_profit;
  }

  public function getBalanceAttribute(): float
  {
    return $this->final_profit + $this->remaining_budget + $this->ml_expenditure;
  }
}