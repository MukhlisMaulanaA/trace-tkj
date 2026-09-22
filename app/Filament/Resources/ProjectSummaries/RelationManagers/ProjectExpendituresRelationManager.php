<?php

namespace App\Filament\Resources\ProjectSummaries\RelationManagers;

use Filament\Tables\Table;
use Filament\Support\RawJs;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use App\Models\ProjectExpenditure;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\RelationManagers\RelationManager;

class ProjectExpendituresRelationManager extends RelationManager
{
  protected static string $relationship = 'expenditures';

  protected static ?string $title = 'Material and Labour Expenditure';

  public function isReadOnly(): bool
  {
    return false;
  }

  public function form(Schema $schema): Schema
  {
    return $schema->components([
      Section::make('Expenditure Details')
        ->description('Enter the expenditure information below.')
        ->icon('heroicon-o-receipt-percent')
        ->schema([
          Select::make('type')
            ->label('Tipe Pengeluaran')
            ->options([
              ProjectExpenditure::TYPE_MATERIAL => 'Material',
              ProjectExpenditure::TYPE_LABOUR => 'Labour',
            ])
            ->native(false)
            ->required()
            ->helperText('Choose whether this expense is Material or Labour.'),

          DatePicker::make('expenditure_date')
            ->label('Expenditure Date')
            ->required()
            ->default(now())
            ->native(false),

          TextInput::make('description')
            ->label('Description')
            ->placeholder('e.g. Cable NYY 4×10 mm, Installation Labour')
            ->required()
            ->maxLength(255)
            ->columnSpanFull(),

          TextInput::make('amount')
            ->label('Expenditure Amount')
            ->prefix('Rp')
            ->placeholder('0')
            ->mask(RawJs::make(<<<'JS'
                        $input
                            .replace(/\D/g, '')
                            .replace(/\B(?=(\d{3})+(?!\d))/g, ',')
                    JS))
            ->stripCharacters(',')
            ->numeric()
            ->minValue(0)
            ->required()
            ->helperText('Enter the amount in Rupiah. Example: Rp 1,500,000.')
            ->columnSpanFull(),
        ])
        ->columns(2),
    ]);
  }

  public function table(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('type')->badge(),
        TextColumn::make('description')->searchable(),
        TextColumn::make('amount')->money('IDR', 0),
        TextColumn::make('expenditure_date')->date(),
      ])
      ->defaultSort('expenditure_date', 'desc')
      ->headerActions([CreateAction::make()])
      ->recordActions([EditAction::make(), DeleteAction::make()]);
  }
}