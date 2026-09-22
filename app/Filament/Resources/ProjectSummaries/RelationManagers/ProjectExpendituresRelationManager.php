<?php

namespace App\Filament\Resources\ProjectSummaries\RelationManagers;

use App\Models\ProjectExpenditure;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
      Section::make('Expenditure')->schema([
        Select::make('type')
          ->label('Type')
          ->options([
            ProjectExpenditure::TYPE_MATERIAL => 'Material',
            ProjectExpenditure::TYPE_LABOUR => 'Labour',
          ])
          ->required(),
        TextInput::make('description')->required(),
        TextInput::make('amount')->label('Value')->numeric()->prefix('Rp')->required(),
        DatePicker::make('expenditure_date')->label('Date')->required(),
      ])->columns(2),
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