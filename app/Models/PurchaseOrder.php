<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
  use HasFactory;

  /*
  |--------------------------------------------------------------------------
  | MODEL EVENTS
  |--------------------------------------------------------------------------
  */

  protected static function booted(): void
  {
    static::creating(function (PurchaseOrder $purchaseOrder) {
      if (empty($purchaseOrder->po_code)) {
        $purchaseOrder->po_code = static::generatePoCode();
      }

      if (empty($purchaseOrder->status)) {
        $purchaseOrder->status = 'draft';
      }
    });

    static::saving(function (PurchaseOrder $purchaseOrder) {
      $purchaseOrder->recalculateTotals();
    });

    static::saved(function (PurchaseOrder $purchaseOrder) {
      if (
        $purchaseOrder->wasChanged([
          'subtotal',
          'ppn_enabled',
        ])
      ) {
        $purchaseOrder->calculateTotals();
      }
    });
  }

  /*
  |--------------------------------------------------------------------------
  | GENERATE PO CODE
  |--------------------------------------------------------------------------
  |
  | Format:
  | PO26H001
  | PO26H002
  | PO26H003
  |
  */

  public static function generatePoCode(): string
  {
    $year = date('y');

    $lastPo = static::query()
      ->where('po_code', 'LIKE', "PO{$year}H%")
      ->orderBy('po_code', 'desc')
      ->first();

    if ($lastPo) {
      $lastSequence = (int) substr($lastPo->po_code, -3);
      $newSequence = sprintf('%03d', $lastSequence + 1);
    } else {
      $newSequence = '001';
    }

    return "PO{$year}H{$newSequence}";
  }

  /*
  |--------------------------------------------------------------------------
  | FILLABLE
  |--------------------------------------------------------------------------
  */

  protected $fillable = [
    'po_code',
    'po_number',
    'po_date',
    'project_id',
    'customer',
    'location',
    'quotation_no',
    'pic',
    'status',
    'notes',
    'subtotal',
    'ppn_enabled',
    'ppn_amount',
    'grand_total',
    'discount_enabled',
    'discount_percent',
    'discount_amount',
    'grand_total',
  ];

  /*
  |--------------------------------------------------------------------------
  | CASTS
  |--------------------------------------------------------------------------
  */

  protected function casts(): array
  {
    return [
      'po_date' => 'date',
      'subtotal' => 'decimal:0',
      'ppn_enabled' => 'boolean',
      'ppn_amount' => 'decimal:0',
      'discount_enabled' => 'boolean',
      'discount_percent' => 'decimal:2',
      'discount_amount' => 'decimal:2',
      'grand_total' => 'decimal:2',
    ];
  }

  /*
  |--------------------------------------------------------------------------
  | RELATIONSHIPS
  |--------------------------------------------------------------------------
  */

  public function project(): BelongsTo
  {
    return $this->belongsTo(
      Project::class,
      'project_id',
      'id'
    );
  }

  public function items(): HasMany
  {
    return $this->hasMany(
      PurchaseOrderItem::class,
      'purchase_order_id'
    )->orderBy('item_no');
  }

  /*
  |--------------------------------------------------------------------------
  | TOTAL CALCULATION
  |--------------------------------------------------------------------------
  */

  public function calculateTotals(): void
  {
    $subtotal = $this->items()->sum('total_price');

    /*
     * Client requirement:
     *
     * Display : PPN 12%
     * Calculate: 11%
     */

    $ppnAmount = $this->ppn_enabled
      ? round($subtotal * 0.11)
      : 0;

    $grandTotal = $subtotal + $ppnAmount;

    $this->forceFill([
      'subtotal' => $subtotal,
      'ppn_amount' => $ppnAmount,
      'grand_total' => $grandTotal,
    ])->saveQuietly();
  }

  /*
  |--------------------------------------------------------------------------
  | STATUS HELPERS
  |--------------------------------------------------------------------------
  */

  public function isDraft(): bool
  {
    return $this->status === 'draft';
  }

  public function isSubmitted(): bool
  {
    return $this->status === 'submitted';
  }

  public function recalculateTotals(): void
  {
    $subtotal = (float) ($this->subtotal ?? 0);

    /*
    |--------------------------------------------------------------------------
    | PPN
    |--------------------------------------------------------------------------
    | Display : 12%
    | Actual  : 11%
    |--------------------------------------------------------------------------
    */

    $ppnAmount = 0;

    if ($this->ppn_enabled) {
      $ppnAmount = round($subtotal * 0.11);
    }

    /*
    |--------------------------------------------------------------------------
    | TOTAL SETELAH PPN
    |--------------------------------------------------------------------------
    */

    $totalAfterPpn = $subtotal + $ppnAmount;

    /*
    |--------------------------------------------------------------------------
    | DISCOUNT
    |--------------------------------------------------------------------------
    */

    $discountAmount = 0;

    if ($this->discount_enabled) {
      $discountPercent = max(
        0,
        min(100, (float) ($this->discount_percent ?? 0))
      );

      $discountAmount = round(
        $totalAfterPpn * ($discountPercent / 100)
      );
    }

    /*
    |--------------------------------------------------------------------------
    | GRAND TOTAL
    |--------------------------------------------------------------------------
    */

    $grandTotal = max(
      0,
      $totalAfterPpn - $discountAmount
    );

    $this->ppn_amount = $ppnAmount;
    $this->discount_amount = $discountAmount;
    $this->grand_total = $grandTotal;
  }
}