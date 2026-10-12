<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Models\Invoice;
use App\Models\PurchaseOrder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;

class InvoiceForm
{
  public static function configure(Schema $schema): Schema
  {
    return $schema->components([
      Section::make('Invoice Details')->schema([
        Grid::make(['default' => 1, 'md' => 2])->schema([
          TextInput::make('invoice_code')->label('invoice_code')->disabled()->dehydrated(false)->placeholder('Otomatis'),
          TextInput::make('invoice_number')->label('Invoice Number')->required()->unique(ignoreRecord: true),
          DatePicker::make('invoice_date')->label('Invoice Date')->default(now())->required()->native(false),
          TextInput::make('recipient')->label('To')->required(),
          Select::make('purchase_order_id')
            ->label('Purchase Order Number')
            ->relationship('purchaseOrder', 'po_number', modifyQueryUsing: fn(Builder $query): Builder => $query->accessibleBy(auth()->user()))
            ->getOptionLabelFromRecordUsing(fn(PurchaseOrder $record): string => "{$record->po_number} ({$record->po_code})")
            ->searchable(['po_number', 'po_code'])
            ->preload()
            ->live()
            ->required()
            ->afterStateUpdated(function (?string $state, Set $set): void {
              $purchaseOrder = blank($state) ? null : PurchaseOrder::accessibleBy(auth()->user())->find($state);
              $set('project_name', $purchaseOrder?->project?->nama_project);
            })
            ->columnSpanFull(),
          TextInput::make('project_name')->label('Project Name')->required(),
          Select::make('status')->options([
            'draft' => 'Draft',
            'issued' => 'Issued',
            'paid' => 'Paid',
            'cancelled' => 'Cancelled',
          ])->default('draft')->required(),
        ]),
      ]),
      Section::make('Payment Details')->schema([
        Grid::make(['default' => 1, 'md' => 2])->schema([
          Select::make('payment_type')->label('Payment Type')->options([
            'full_payment' => 'Full Payment',
            'down_payment' => 'Down Payment',
          ])->default('full_payment')->required(),
          Select::make('payment_method')->label('Payment Method')->options(self::paymentMethods())->default('mandiri')->required()->live(),
          Placeholder::make('payment_account')->label('Payment Account')->content(fn(Get $get): string => self::paymentAccount($get('payment_method')))->columnSpanFull(),
          TextInput::make('payment_progress')->label('Payment Progress')->placeholder('Contoh: 50% / Termin 1'),
          TextInput::make('payment_progress_amount')->label('Payment Progress Amount')->prefix('Rp')->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(',')->disabled()->dehydrated(false),
          Textarea::make('payment_notes')->label('Payment Notes')->rows(2)->columnSpanFull(),
        ]),
      ]),
      Section::make('Calculations')->schema([

        // 1. Bagian Diskon
        Toggle::make('discount_enabled')
          ->label('Aktifkan Diskon')
          ->live()
          ->default(false)
          ->columnSpanFull() // Jadikan satu baris penuh di atas
          ->afterStateUpdated(function (bool $state, Set $set): void {
            if (!$state) {
              $set('discount_type', 'percent');
              $set('discount_percent', 0);
              $set('discount_amount', 0);
            }
          }),

        // Grid ini membungkus input diskon agar sejajar (muncul jika toggle aktif)
        Grid::make(['default' => 1, 'md' => 2])
          ->visible(fn(Get $get): bool => (bool) $get('discount_enabled'))
          ->schema([
            Select::make('discount_type')
              ->label('Metode Diskon')
              ->options(['percent' => 'Persentase', 'amount' => 'Nominal'])
              ->default('percent')
              ->live()
              ->required(),

            TextInput::make('discount_percent')
              ->label('Diskon')
              ->suffix('%')
              ->numeric()
              ->minValue(0)
              ->maxValue(100)
              ->default(0)
              ->visible(fn(Get $get): bool => $get('discount_type') === 'percent')
              ->required(fn(Get $get): bool => $get('discount_type') === 'percent'),

            TextInput::make('discount_amount')
              ->label('Nilai Diskon')
              ->prefix('Rp')
              ->numeric()
              ->mask(RawJs::make('$money($input)'))
              ->stripCharacters(',')
              ->default(0)
              ->visible(fn(Get $get): bool => $get('discount_type') === 'amount')
              ->required(fn(Get $get): bool => $get('discount_type') === 'amount'),
          ]),

        // 2. Bagian Pajak (DPP & PPN disejajarkan Kiri & Kanan)
        Grid::make(['default' => 1, 'md' => 2])->schema([

          // Kolom Kiri: Khusus DPP (Toggle & Input menumpuk rapi)
          Grid::make(1)->columnSpan(1)->schema([
            Toggle::make('dpp_enabled')
              ->label('Aktifkan DPP')
              ->default(false)
              ->live(),

            TextInput::make('dpp_display')
              ->label('DPP')
              ->formatStateUsing(fn(): string => '0,916666666666667')
              ->disabled()
              ->dehydrated(false)
              ->visible(fn(Get $get): bool => (bool) $get('dpp_enabled'))
              ->helperText('Faktor DPP bersifat baku dan tidak dapat diubah.'),
          ]),

          // Kolom Kanan: Khusus PPN (Toggle & Input menumpuk rapi)
          Grid::make(1)->columnSpan(1)->schema([
            Toggle::make('ppn_enabled')
              ->label('Aktifkan PPN')
              ->default(false)
              ->live(),

            TextInput::make('ppn_display')
              ->label('Tarif PPN')
              ->formatStateUsing(fn($state, Get $get): string => (bool) $get('dpp_enabled') ? '12%' : '11%')
              ->disabled()
              ->dehydrated(false)
              ->visible(fn(Get $get): bool => (bool) $get('ppn_enabled')) // Disembunyikan saat PPN mati agar UI lebih bersih
              ->helperText('DPP ON: 12%. PPN OFF: 11%.'),
          ]),

        ]),
      ]),
    ]);
  }

  public static function paymentMethods(): array
  {
    return [
      'mandiri' => 'Bank Mandiri',
      'bca_ilham' => 'Bank BCA (ILHAM JAWAZ)',
      'bca_rohiman' => 'Bank BCA (ROHIMAN)',
    ];
  }

  public static function paymentAccount(?string $method): string
  {
    return match ($method) {
      'bca_ilham' => 'No. Rekening: 521-139-9741 | Nama: ILHAM JAWAZ',
      'bca_rohiman' => 'No. Rekening: 741-053-9483 | Nama: ROHIMAN',
      default => 'No. Rekening: 156-00-2075315-0 | Nama: PT. TANJUNG KARYA JAYA',
    };
  }
}