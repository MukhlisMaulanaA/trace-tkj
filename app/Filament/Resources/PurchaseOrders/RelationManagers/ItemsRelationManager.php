<?php

namespace App\Filament\Resources\PurchaseOrders\RelationManagers;

use App\Models\PurchaseOrderItem;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
  protected static string $relationship = 'items';

  protected static ?string $title = 'Item Purchase Order';

  protected static ?string $recordTitleAttribute = 'description';

  /*
  |--------------------------------------------------------------------------
  | FORM
  |--------------------------------------------------------------------------
  */

  public function form(Schema $schema): Schema
  {
    return $schema
      ->components([
        Section::make('Informasi Item')
          ->description('Masukkan detail item Purchase Order.')
          ->schema([
            Grid::make([
              'default' => 1,
              'md' => 2,
            ])
              ->schema([
                TextInput::make('section')
                  ->label('Section / Grup')
                  ->placeholder('Contoh: PIPING WORK')
                  ->helperText('Opsional. Gunakan untuk mengelompokkan item.'),

                Textarea::make('description')
                  ->label('Description')
                  ->required()
                  ->rows(3)
                  ->placeholder('Deskripsi item...')
                  ->columnSpanFull(),

                TextInput::make('quantity')
                  ->label('Qty')
                  ->numeric()
                  ->minValue(0)
                  ->required()
                  ->default(1)
                  ->live(onBlur: true)
                  ->afterStateUpdated(
                    fn(Get $get, Set $set) =>
                    self::updateTotalPrice($get, $set)
                  ),

                TextInput::make('sat')
                  ->label('SAT')
                  ->required()
                  ->placeholder('PCS, UNIT, BTG, LOT, M, dll.'),

                TextInput::make('unit_price')
                  ->label('Unit Price')
                  ->numeric()
                  ->minValue(0)
                  ->required()
                  ->default(0)
                  ->prefix('Rp')
                  ->live(onBlur: true)
                  ->afterStateUpdated(
                    fn(Get $get, Set $set) =>
                    self::updateTotalPrice($get, $set)
                  ),

                TextInput::make('total_price')
                  ->label('Total Price')
                  ->numeric()
                  ->prefix('Rp')
                  ->disabled()
                  ->dehydrated()
                  ->default(0),

                Textarea::make('notes')
                  ->label('Notes')
                  ->placeholder('Keterangan tambahan untuk item...')
                  ->rows(3)
                  ->columnSpanFull(),
              ]),
          ]),
      ]);
  }

  /*
  |--------------------------------------------------------------------------
  | TABLE
  |--------------------------------------------------------------------------
  */

  public function table(Table $table): Table
  {
    return $table
      ->defaultSort('item_no', 'asc')
      ->paginated(false)
      ->striped(false)
      ->columns([
        TextColumn::make('item_no')
          ->label('No.')
          ->width('70px')
          ->alignCenter(),

        TextColumn::make('section')
          ->label('Section')
          ->placeholder('-')
          ->badge()
          ->color('gray')
          ->toggleable(),

        TextColumn::make('description')
          ->label('Description')
          ->wrap()
          ->searchable(),

        TextColumn::make('quantity')
          ->label('Qty')
          ->numeric(decimalPlaces: 2)
          ->alignRight(),

        TextColumn::make('sat')
          ->label('SAT')
          ->alignCenter(),

        TextColumn::make('unit_price')
          ->label('Unit Price')
          ->money('IDR')
          ->alignRight(),

        TextColumn::make('total_price')
          ->label('Total Price')
          ->money('IDR')
          ->alignRight()
          ->weight('bold'),

        TextColumn::make('notes')
          ->label('Notes')
          ->limit(40)
          ->tooltip(
            fn(?string $state): ?string => $state
          )
          ->placeholder('-')
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->headerActions([
        CreateAction::make()
          ->label('Tambah Item')
          ->icon('heroicon-o-plus')
          ->visible(
            fn(): bool =>
            $this->getOwnerRecord()->status === 'draft'
          ),
      ])
      ->recordActions([
        EditAction::make()
          ->label('Update')
          ->icon('heroicon-o-pencil-square')
          ->visible(
            fn(): bool =>
            $this->getOwnerRecord()->status === 'draft'
          ),

        DeleteAction::make()
          ->label('Delete')
          ->icon('heroicon-o-trash')
          ->requiresConfirmation()
          ->visible(
            fn(): bool =>
            $this->getOwnerRecord()->status === 'draft'
          ),
      ]);
  }

  /*
  |--------------------------------------------------------------------------
  | AUTO CALCULATE TOTAL
  |--------------------------------------------------------------------------
  */

  protected static function updateTotalPrice(
    Get $get,
    Set $set
  ): void {
    $quantity = (float) ($get('quantity') ?? 0);
    $unitPrice = (float) ($get('unit_price') ?? 0);

    $total = $quantity * $unitPrice;

    $set('total_price', number_format($total, 2, '.', ''));
  }
}