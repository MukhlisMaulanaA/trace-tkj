<?php

namespace App\Filament\Resources\ProjectSummaries;

use App\Filament\Resources\ProjectSummaries\Pages\EditProjectSummary;
use App\Filament\Resources\ProjectSummaries\Pages\ListProjectSummaries;
use App\Filament\Resources\ProjectSummaries\Pages\ViewProjectSummary;
use App\Filament\Resources\ProjectSummaries\RelationManagers\ProjectExpendituresRelationManager;
use App\Models\ProjectSummary;
use BackedEnum;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProjectSummaryResource extends Resource
{
  protected static ?string $model = ProjectSummary::class;

  protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

  protected static ?string $navigationLabel = 'Project Summaries';

  public static function form(Schema $schema): Schema
  {
    return $schema->components([
      Radio::make('profit_mode')
        ->label('Profit Mode')
        ->options([
          ProjectSummary::PROFIT_MODE_PERCENTAGE => 'Percentage',
          ProjectSummary::PROFIT_MODE_NOMINAL => 'Nominal',
        ])
        ->default(ProjectSummary::PROFIT_MODE_PERCENTAGE)
        ->required()
        ->live(),
      TextInput::make('profit_percentage')
        ->label('Profit Percentage')
        ->numeric()
        ->suffix('%')
        ->minValue(0)
        ->maxValue(100)
        ->visible(fn ($get): bool => $get('profit_mode') === ProjectSummary::PROFIT_MODE_PERCENTAGE)
        ->required(fn ($get): bool => $get('profit_mode') === ProjectSummary::PROFIT_MODE_PERCENTAGE),
      TextInput::make('nominal_profit')
        ->label('Nominal Profit')
        ->numeric()
        ->prefix('Rp')
        ->minValue(0)
        ->visible(fn ($get): bool => $get('profit_mode') === ProjectSummary::PROFIT_MODE_NOMINAL)
        ->required(fn ($get): bool => $get('profit_mode') === ProjectSummary::PROFIT_MODE_NOMINAL),
    ]);
  }

  public static function table(Table $table): Table
  {
    return $table->columns([
      TextColumn::make('project.kustomer')->label('Owner / Customer')->searchable(),
      TextColumn::make('project_name')->label('Project Name')->searchable(),
      TextColumn::make('po_numbers')->label('PO Number'),
      TextColumn::make('final_contract_value')->label('Final Contract Value')->money('IDR', 0),
      TextColumn::make('final_profit')->label('Final Profit')->money('IDR', 0),
      TextColumn::make('remaining_budget')->label('Remaining Budget')->money('IDR', 0),
    ]);
  }

  public static function getRelations(): array
  {
    return [ProjectExpendituresRelationManager::class];
  }

  public static function getPages(): array
  {
    return [
      'index' => ListProjectSummaries::route('/'),
      'view' => ViewProjectSummary::route('/{record}'),
      'edit' => EditProjectSummary::route('/{record}/edit'),
    ];
  }
}