<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
  use HasFactory;

  protected $fillable = [
    'purchase_order_id',
    'item_no',
    'section',
    'description',
    'quantity',
    'sat',
    'unit_price',
    'notes',
    'total_price',
  ];

  protected function casts(): array
  {
    return [
      'quantity' => 'decimal:2',
      'unit_price' => 'decimal:2',
      'total_price' => 'decimal:2',
    ];
  }

  /*
  |--------------------------------------------------------------------------
  | RELATIONSHIP
  |--------------------------------------------------------------------------
  */

  public function purchaseOrder(): BelongsTo
  {
    return $this->belongsTo(
      PurchaseOrder::class,
      'purchase_order_id'
    );
  }

  /*
  |--------------------------------------------------------------------------
  | MODEL EVENTS
  |--------------------------------------------------------------------------
  */

  protected static function booted(): void
  {
    /*
    |--------------------------------------------------------------------------
    | Generate item_no automatically
    |--------------------------------------------------------------------------
    */

    static::creating(function (PurchaseOrderItem $item) {
      $lastItemNo = static::query()
        ->where(
          'purchase_order_id',
          $item->purchase_order_id
        )
        ->max('item_no');

      $item->item_no = ((int) $lastItemNo) + 1;
    });

    /*
    |--------------------------------------------------------------------------
    | Calculate total price
    |--------------------------------------------------------------------------
    */

    static::saving(function (PurchaseOrderItem $item) {
      $item->total_price =
        (float) $item->quantity *
        (float) $item->unit_price;
    });

    /*
    |--------------------------------------------------------------------------
    | Update PO subtotal after item saved
    |--------------------------------------------------------------------------
    */

    static::saved(function (PurchaseOrderItem $item) {
      $item->purchaseOrder?->calculateTotals();
    });

    /*
    |--------------------------------------------------------------------------
    | Update PO subtotal after item deleted
    |--------------------------------------------------------------------------
    */

    static::deleted(function (PurchaseOrderItem $item) {
      $item->purchaseOrder?->calculateTotals();
    });
  }

  /*
  |--------------------------------------------------------------------------
  | UPDATE PURCHASE ORDER SUBTOTAL
  |--------------------------------------------------------------------------
  */

  // public function updatePurchaseOrderSubtotal(): void
  // {
  //   $subtotal = static::query()
  //     ->where(
  //       'purchase_order_id',
  //       $this->purchase_order_id
  //     )
  //     ->sum('total_price');

  //   $this->purchaseOrder()->update([
  //     'subtotal' => $subtotal,
  //   ]);
  // }
}