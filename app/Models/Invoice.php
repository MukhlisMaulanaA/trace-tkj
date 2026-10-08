<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
  use HasFactory;

  protected const DPP_FACTOR = 0.916666666666667;

  protected $fillable = [
    'invoice_code',
    'recipient',
    'purchase_order_id',
    'project_name',
    'invoice_date',
    'payment_type',
    'payment_progress',
    'payment_progress_amount',
    'payment_notes',
    'payment_method',
    'status',
    'subtotal',
    'discount_enabled',
    'discount_type',
    'discount_percent',
    'discount_amount',
    'dpp_enabled',
    'dpp_amount',
    'ppn_enabled',
    'ppn_amount',
    'grand_total',
  ];

  protected function casts(): array
  {
    return [
      'invoice_date' => 'date',
      'payment_progress_amount' => 'decimal:2',
      'subtotal' => 'decimal:0',
      'discount_enabled' => 'boolean',
      'discount_percent' => 'decimal:2',
      'discount_amount' => 'decimal:0',
      'dpp_enabled' => 'boolean',
      'dpp_amount' => 'decimal:0',
      'ppn_enabled' => 'boolean',
      'ppn_amount' => 'decimal:0',
      'grand_total' => 'decimal:0',
    ];
  }

  protected static function booted(): void
  {
    static::creating(function (Invoice $invoice): void {
      if (blank($invoice->invoice_code)) {
        $invoice->invoice_code = static::generateInvoiceCode();
      }

      $invoice->status ??= 'draft';
    });

    static::saved(function (Invoice $invoice): void {
      $invoice->calculateTotals();
    });
  }

  public static function generateInvoiceCode(): string
  {
    $year = date('y');
    $lastInvoice = static::query()
      ->where('invoice_code', 'like', "INV{$year}J%")
      ->orderByDesc('invoice_code')
      ->first();

    $sequence = $lastInvoice ? ((int) substr($lastInvoice->invoice_code, -3)) + 1 : 1;

    return "INV{$year}J" . sprintf('%03d', $sequence);
  }

  public function purchaseOrder(): BelongsTo
  {
    return $this->belongsTo(PurchaseOrder::class);
  }

  public function items(): HasMany
  {
    return $this->hasMany(InvoiceItem::class)->orderBy('item_no');
  }

  public function calculateTotals(): void
  {
    $subtotal = (float) $this->items()->sum('amount');
    $discountAmount = 0;
    $discountPercent = 0;

    if ($this->discount_enabled) {
      if ($this->discount_type === 'amount') {
        $discountAmount = max(0, min($subtotal, round((float) $this->discount_amount)));
        $discountPercent = $subtotal > 0 ? round(($discountAmount / $subtotal) * 100, 2) : 0;
      } else {
        $discountPercent = max(0, min(100, (float) $this->discount_percent));
        $discountAmount = round($subtotal * ($discountPercent / 100));
      }
    }

    $subtotalAfterDiscount = max(0, $subtotal - $discountAmount);
    $dppAmount = $this->dpp_enabled ? round($subtotalAfterDiscount * self::DPP_FACTOR) : 0;
    $ppnAmount = 0;

    if ($this->ppn_enabled) {
      $ppnAmount = $this->dpp_enabled
        ? round($dppAmount * 0.12)
        : round($subtotalAfterDiscount * 0.11);
    }

    $this->forceFill([
      'subtotal' => $subtotal,
      'discount_percent' => $discountPercent,
      'discount_amount' => $discountAmount,
      'dpp_amount' => $dppAmount,
      'ppn_amount' => $ppnAmount,
      'grand_total' => max(0, round($subtotalAfterDiscount + $ppnAmount)),
    ])->saveQuietly();
  }

  public function scopeAccessibleBy($query, ?User $user = null)
  {
    $user ??= auth()->user();

    return $query->whereHas('purchaseOrder', fn($poQuery) => $poQuery->accessibleBy($user));
  }
}