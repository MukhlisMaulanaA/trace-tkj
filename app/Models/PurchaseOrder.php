<?php

namespace App\Models;

use App\Models\PurchaseOrderItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
  use HasFactory;

  protected static function booted(): void
  {
    static::creating(function (PurchaseOrder $purchaseOrder) {
      if (empty($purchaseOrder->po_number)) {
        $purchaseOrder->po_number = static::generatePoNumber();
      }

      if (empty($purchaseOrder->status)) {
        $purchaseOrder->status = 'draft';
      }
    });
  }

  public static function generatePoNumber(): string
  {
    $year = date('Y');

    $lastPo = static::where('po_number', 'LIKE', "PO-{$year}-%")
      ->orderBy('po_number', 'desc')
      ->first();

    if ($lastPo) {
      $lastSequence = (int) substr($lastPo->po_number, -3);
      $newSequence = sprintf('%03d', $lastSequence + 1);
    } else {
      $newSequence = '001';
    }

    return "PO-{$year}-{$newSequence}";
  }

  protected $fillable = [
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
  ];

  protected function casts(): array
  {
    return [
      'po_date' => 'date',
      'subtotal' => 'decimal:2',
    ];
  }

  public function project(): BelongsTo
  {
    return $this->belongsTo(Project::class, 'project_id', 'id');
  }

  public function items(): HasMany
  {
    return $this->hasMany(
      PurchaseOrderItem::class,
      'purchase_order_id'
    )->orderBy('item_no');
  }

  public function isDraft(): bool
  {
    return $this->status === 'draft';
  }

  public function isSubmitted(): bool
  {
    return $this->status === 'submitted';
  }

}