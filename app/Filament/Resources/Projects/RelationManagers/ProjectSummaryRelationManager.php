<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use App\Filament\Resources\ProjectSummaries\ProjectSummaryResource;
use App\Models\ProjectSummary;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProjectSummaryRelationManager extends RelationManager
{
  protected static string $relationship = 'summary';

  protected static ?string $title = 'Project Summary';

  public function isReadOnly(): bool
  {
    return false;
  }

  public function table(Table $table): Table
  {
    return $table
      ->heading('Project Summary')
      ->columns([
        TextColumn::make('owner_customer')
          ->label('Owner / Customer'),
        TextColumn::make('project_name')
          ->label('Project Name'),
        TextColumn::make('po_numbers')
          ->label('PO Number'),
        TextColumn::make('contract_value')
          ->label('Contract Value')
          ->money('IDR', 0),
        TextColumn::make('pph_amount')
          ->label('PPH 2.65%')
          ->money('IDR', 0),
        TextColumn::make('final_contract_value')
          ->label('Final Contract Value')
          ->money('IDR', 0),
        TextColumn::make('final_profit')
          ->label('Final Profit')
          ->money('IDR', 0),
        TextColumn::make('remaining_budget')
          ->label('Remaining Budget')
          ->money('IDR', 0),
        TextColumn::make('balance')
          ->label('Balance')
          ->money('IDR', 0),
      ])
      ->recordUrl(fn (ProjectSummary $record): string => ProjectSummaryResource::getUrl('view', ['record' => $record]));
  }
}