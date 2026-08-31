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
   * Get cumulative total of all invoices up to (and including) this record.
   *
   * @return float
   */
  public function getCumulativeTotal(): float
  {
    return (float) $this->purchaseOrder
      ->progresses()
      ->where('invoice_date', '<=', $this->invoice_date)
      ->sum('amount');
  }

  /**
   * Calculate the progress percentage based on cumulative invoice amounts and PO grand total.
   *
   * @return void
   */
  public function calculatePercentage(): void
  {
    // Get the PO's grand_total - use fresh query if relationship not loaded
    $grandTotal = 0;
    
    if ($this->purchase_order_id) {
      if ($this->relationLoaded('purchaseOrder') && $this->purchaseOrder) {
        $grandTotal = $this->purchaseOrder->grand_total ?? 0;
      } else {
        // Fetch fresh from database if relationship not loaded
        $po = PurchaseOrder::find($this->purchase_order_id);
        $grandTotal = $po?->grand_total ?? 0;
      }
    }

    if ($grandTotal <= 0) {
      $this->percentage = 0;
    } else {
      // Calculate cumulative percentage
      $cumulativeAmount = $this->getCumulativeTotal();
      $this->percentage = round(($cumulativeAmount / $grandTotal) * 100, 2);
    }
  }

  /**
   * Automatically calculate percentage before saving.
   */
  protected static function booted(): void
  {
    static::saving(function (PurchaseOrderProgress $progress) {
      $progress->calculatePercentage();
    });
  }

  public function purchaseOrder(): BelongsTo
  {
    return $this->belongsTo(PurchaseOrder::class);
  }
}

