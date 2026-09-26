<?php

namespace App\Filament\Resources\PurchaseOrders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class PurchaseOrdersTable
{
  public static function configure(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('po_number')
          ->searchable(),
        TextColumn::make('po_date')
          ->date()
          ->sortable(),
        TextColumn::make('project.id')
          ->searchable(),
        TextColumn::make('customer')
          ->searchable(),
        TextColumn::make('location')
          ->searchable(),
        TextColumn::make('quotation_no')
          ->searchable(),
        TextColumn::make('pic')
          ->searchable(),
        TextColumn::make('status')
          ->badge(),
        TextColumn::make('subtotal')
          ->numeric()
          ->sortable(),
        TextColumn::make('created_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('updated_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->filters([
        SelectFilter::make('project_source')
          ->label('Sumber Project')
          ->options([
            'pusat' => 'Pusat',
            'distrik_8' => 'Distrik 8',
          ])
          ->placeholder('Semua Sumber')
          ->visible(fn(): bool => auth()->user()?->isPusat() ?? false)
          ->query(function (Builder $query, array $data): Builder {
            $value = $data['value'] ?? null;

            if (blank($value)) {
              return $query;
            }

            return $query->whereHas(
              'project',
              fn(Builder $projectQuery) =>
                $projectQuery->where(
                  'project_source',
                  $value
                )
            );
          }),
      ])
      ->recordActions([
        ViewAction::make(),
        EditAction::make(),
      ])
      ->toolbarActions([
        BulkActionGroup::make([
          DeleteBulkAction::make(),
        ]),
      ]);
  }
}
