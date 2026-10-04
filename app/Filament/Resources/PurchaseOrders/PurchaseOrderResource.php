<?php

namespace App\Filament\Resources\PurchaseOrders;

use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\PurchaseOrder;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PurchaseOrders\Pages\EditPurchaseOrder;
use App\Filament\Resources\PurchaseOrders\Pages\ViewPurchaseOrder;
use App\Filament\Resources\PurchaseOrders\Pages\ListPurchaseOrders;
use App\Filament\Resources\PurchaseOrders\Pages\CreatePurchaseOrder;
use App\Filament\Resources\PurchaseOrders\Schemas\PurchaseOrderForm;
use App\Filament\Resources\PurchaseOrders\Tables\PurchaseOrdersTable;
use App\Filament\Resources\PurchaseOrders\Schemas\PurchaseOrderInfolist;
use App\Filament\Resources\PurchaseOrders\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\PurchaseOrders\RelationManagers\PurchaseOrderProgressRelationManager;
use UnitEnum;

class PurchaseOrderResource extends Resource
{
  protected static ?string $model = PurchaseOrder::class;

  protected static string|BackedEnum|null $navigationIcon =
    Heroicon::OutlinedDocumentText;

  protected static ?string $recordTitleAttribute = 'po_number';

  protected static string | UnitEnum | null $navigationGroup = 'Project Management';


  public static function form(Schema $schema): Schema
  {
    return PurchaseOrderForm::configure($schema);
  }

  public static function infolist(Schema $schema): Schema
  {
    return PurchaseOrderInfolist::configure($schema);
  }

  public static function table(Table $table): Table
  {
    return PurchaseOrdersTable::configure($table);
  }

  public static function getRelations(): array
  {
    return [
      ItemsRelationManager::class,
      PurchaseOrderProgressRelationManager::class,
    ];
  }

  public static function getPages(): array
  {
    return [
      'index' => ListPurchaseOrders::route('/'),
      'create' => CreatePurchaseOrder::route('/create'),
      'view' => ViewPurchaseOrder::route('/{record}'),
      'edit' => EditPurchaseOrder::route('/{record}/edit'),
    ];
  }

  public static function getEloquentQuery(): Builder
  {
    return parent::getEloquentQuery()
      ->accessibleBy(auth()->user());
  }
}