<?php

namespace App\Filament\Resources\Invoices\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\RawJs;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
  protected static string $relationship = 'items';
  protected static ?string $title = 'Invoice Items';
  protected static ?string $recordTitleAttribute = 'description';

  public function isReadOnly(): bool
  {
    return false;
  }

    public function form(Schema $schema): Schema
  {
    return $schema->components([
      Grid::make(2)->schema([
        Textarea::make('description')->label('Description')->required()->rows(3)->columnSpanFull(),
        TextInput::make('quantity')->label('QTY')->numeric()->minValue(0)->default(1)->required()->live(onBlur: true)->afterStateUpdated(fn(Get $get, Set $set) => self::updateAmount($get, $set)),
        TextInput::make('unit_price')->label('Unit Price')->prefix('Rp')->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(',')->default(0)->required()->live(onBlur: true)->afterStateUpdated(fn(Get $get, Set $set) => self::updateAmount($get, $set)),
        TextInput::make('amount')->label('Amount')->prefix('Rp')->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(',')->disabled()->dehydrated()->default(0),
      ]),
    ]);
  }

  public function table(Table $table): Table
  {
    return $table->paginated(false)->columns([
      TextColumn::make('item_no')->label('No.'),
      TextColumn::make('description')->label('Description')->wrap(),
      TextColumn::make('quantity')->label('QTY')->numeric(decimalPlaces: 2),
      TextColumn::make('unit_price')->label('Unit Price')->money('IDR')->alignRight(),
      TextColumn::make('amount')->label('Amount')->money('IDR')->alignRight()->weight('bold'),
    ])->headerActions([
          CreateAction::make()->label('Add Item'),
        ])->recordActions([
          EditAction::make(),
          DeleteAction::make()->requiresConfirmation(),
        ]);
  }

  protected static function updateAmount(Get $get, Set $set): void
  {
    $quantity = (float) ($get('quantity') ?? 0);
    $unitPrice = (float) str_replace(',', '', (string) ($get('unit_price') ?? 0));
    $set('amount', number_format($quantity * $unitPrice, 2, '.', ''));
  }
}