<?php

namespace App\Filament\Resources\Invoices;

use App\Filament\Resources\Invoices\Pages\CreateInvoice;
use App\Filament\Resources\Invoices\Pages\EditInvoice;
use App\Filament\Resources\Invoices\Pages\ListInvoices;
use App\Filament\Resources\Invoices\Pages\ViewInvoice;
use App\Filament\Resources\Invoices\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\Invoices\Schemas\InvoiceForm;
use App\Filament\Resources\Invoices\Tables\InvoicesTable;
use App\Models\Invoice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class InvoiceResource extends Resource
{
  protected static ?string $model = Invoice::class;
  protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
  protected static string|UnitEnum|null $navigationGroup = 'Project Management';
  protected static ?string $recordTitleAttribute = 'invoice_code';
  protected static ?string $navigationLabel = 'Invoices';

  public static function form(Schema $schema): Schema
  {
    return InvoiceForm::configure($schema);
  }

  public static function table(Table $table): Table
  {
    return InvoicesTable::configure($table);
  }

  public static function getRelations(): array
  {
    return [ItemsRelationManager::class];
  }

  public static function getPages(): array
  {
    return [
      'index' => ListInvoices::route('/'),
      'create' => CreateInvoice::route('/create'),
      'view' => ViewInvoice::route('/{record}'),
      'edit' => EditInvoice::route('/{record}/edit'),
    ];
  }

  public static function getEloquentQuery(): Builder
  {
    return parent::getEloquentQuery()->accessibleBy(auth()->user());
  }
}