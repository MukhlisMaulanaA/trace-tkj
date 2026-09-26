<?php

namespace App\Filament\Resources\Projects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class ProjectsTable
{
  public static function configure(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('id')
          ->label('ID')
          ->searchable(),
        TextColumn::make('nomor_quotation')
          ->searchable(),
        TextColumn::make('nama_project')
          ->searchable(),
        TextColumn::make('kustomer')
          ->searchable(),
        TextColumn::make('lokasi')
          ->searchable(),
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
          ->visible(fn(): bool => auth()->user()?->isPusat() ?? false),
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
