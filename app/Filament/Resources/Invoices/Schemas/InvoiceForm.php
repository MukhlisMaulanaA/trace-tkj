<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Models\Invoice;
use App\Models\PurchaseOrder;
use Filament\Forms\Components\DatePicker;
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
        Grid::make(2)->schema([
          TextInput::make('invoice_code')->label('Invoice ID')->disabled()->dehydrated(false)->placeholder('Otomatis'),
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
            'draft' => 'Draft', 'issued' => 'Issued', 'paid' => 'Paid', 'cancelled' => 'Cancelled',
          ])->default('draft')->required(),
        ]),
      ]),
      Section::make('Payment Details')->schema([
        Grid::make(2)->schema([
          Select::make('payment_type')->label('Payment Type')->options([
            'full_payment' => 'Full Payment',
            'down_payment' => 'Down Payment',
          ])->default('full_payment')->required(),
          Select::make('payment_method')->label('Payment Method')->options([
            'mandiri' => 'Bank Mandiri',
            'bca_ilham' => 'Bank BCA (ILHAM JAWAZ)',
            'bca_rohiman' => 'Bank BCA (ROHIMAN)',
          ])->default('mandiri')->required(),
          TextInput::make('payment_progress')->label('Payment Progress')->placeholder('Contoh: 50% / Termin 1'),
          TextInput::make('payment_progress_amount')->label('Payment Progress Amount')->prefix('Rp')->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
          Textarea::make('payment_notes')->label('Payment Notes')->rows(2)->columnSpanFull(),
        ]),
      ]),
      Section::make('Calculations')->schema([
        Toggle::make('discount_enabled')->label('Enable Discount')->live()->default(false),
        Select::make('discount_type')->label('Discount Type')->options(['percent' => 'Percentage', 'amount' => 'Amount'])->default('percent')->visible(fn(Get $get): bool => (bool) $get('discount_enabled'))->live(),
        TextInput::make('discount_percent')->label('Discount')->suffix('%')->numeric()->minValue(0)->maxValue(100)->default(0)->visible(fn(Get $get): bool => (bool) $get('discount_enabled') && $get('discount_type') === 'percent'),
        TextInput::make('discount_amount')->label('Discount Amount')->prefix('Rp')->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(',')->default(0)->visible(fn(Get $get): bool => (bool) $get('discount_enabled') && $get('discount_type') === 'amount'),
        Toggle::make('dpp_enabled')->label('Enable DPP')->default(false),
        Toggle::make('ppn_enabled')->label('Enable VAT / PPN')->default(false),
      ])->columns(2),
    ]);
  }
}