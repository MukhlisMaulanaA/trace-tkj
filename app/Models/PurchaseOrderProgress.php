<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderProgress extends Model
{
  use HasFactory;

  protected $fillable = [
    'purchase_order_id',
    'title',
    'description',
    'invoice_date',
    'pdf_file',
    'amount',
    'percentage',
    'is_system',
  ];

  protected function casts(): array
  {
    return [
      'invoice_date' => 'datetime',
      'amount' => 'decimal:2',
      'percentage' => 'decimal:2',
      'is_system' => 'boolean',
    ];
  }

  /**
   * Calculate invoice percentage based on:
   *
   * invoice amount / PO grand total × 100
   *
   * Percentage represents this invoice itself,
   * not cumulative progress.
   */
  public function calculatePercentage(): void
  {
    $grandTotal = 0;

    if ($this->purchase_order_id) {
      if ($this->relationLoaded('purchaseOrder') && $this->purchaseOrder) {
        $grandTotal = (float) ($this->purchaseOrder->grand_total ?? 0);
      } else {
        $purchaseOrder = PurchaseOrder::find($this->purchase_order_id);

        $grandTotal = (float) ($purchaseOrder?->grand_total ?? 0);
      }
    }

    $amount = (float) ($this->amount ?? 0);

    if ($grandTotal <= 0 || $amount <= 0) {
      $this->percentage = 0;

      return;
    }

    $this->percentage = round(
      max(
        0,
        min(
          100,
          ($amount / $grandTotal) * 100
        )
      ),
      2
    );
  }

  /**
   * Automatically synchronize percentage before saving.
   */
  protected static function booted(): void
  {
    static::saving(function (PurchaseOrderProgress $progress): void {
      $progress->calculatePercentage();
    });
  }

  public function purchaseOrder(): BelongsTo
  {
    return $this->belongsTo(PurchaseOrder::class);
  }
}