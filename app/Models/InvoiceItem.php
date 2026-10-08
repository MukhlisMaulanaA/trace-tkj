<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
  use HasFactory;

  protected $fillable = [
    'invoice_id',
    'item_no',
    'description',
    'quantity',
    'unit_price',
    'amount',
  ];

  protected function casts(): array
  {
    return [
      'quantity' => 'decimal:2',
      'unit_price' => 'decimal:2',
      'amount' => 'decimal:2',
    ];
  }

  protected static function booted(): void
  {
    static::creating(function (InvoiceItem $item): void {
      $item->item_no = ((int) static::where('invoice_id', $item->invoice_id)->max('item_no')) + 1;
    });

    static::saving(function (InvoiceItem $item): void {
      $item->amount = (string) ((float) $item->quantity * (float) $item->unit_price);
    });

    static::saved(fn(InvoiceItem $item) => $item->invoice?->calculateTotals());
    static::deleted(fn(InvoiceItem $item) => $item->invoice?->calculateTotals());
  }

  public function invoice(): BelongsTo
  {
    return $this->belongsTo(Invoice::class);
  }
}