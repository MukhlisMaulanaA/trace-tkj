<?php

namespace App\Filament\Resources\Vendors;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Models\Vendor;
use App\Filament\Resources\Vendors\Pages\CreateVendor;
use App\Filament\Resources\Vendors\Pages\EditVendor;
use App\Filament\Resources\Vendors\Pages\ListVendors;
use App\Filament\Resources\Vendors\Pages\ViewVendor;
use App\Filament\Resources\Vendors\Schemas\VendorForm;
use App\Filament\Resources\Vendors\Schemas\VendorInfolist;
use App\Filament\Resources\Vendors\Tables\VendorsTable;
use UnitEnum;

class VendorResource extends Resource
{
  protected static ?string $model = Vendor::class;

  protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

  protected static ?string $recordTitleAttribute = 'name';

  protected static string|UnitEnum|null $navigationGroup = 'Project Management';

  public static function form(Schema $schema): Schema
  {
    return VendorForm::configure($schema);
  }

  public static function infolist(Schema $schema): Schema
  {
    return VendorInfolist::configure($schema);
  }

  public static function table(Table $table): Table
  {
    return VendorsTable::configure($table);
  }

  public static function getPages(): array
  {
    return [
      'index' => ListVendors::route('/'),
      'create' => CreateVendor::route('/create'),
      'view' => ViewVendor::route('/{record}'),
      'edit' => EditVendor::route('/{record}/edit'),
    ];
  }
}
