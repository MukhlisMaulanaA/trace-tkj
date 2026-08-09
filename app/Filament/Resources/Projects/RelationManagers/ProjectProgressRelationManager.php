<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProjectProgressRelationManager extends RelationManager
{
    protected static string $relationship = 'progresses';

    protected static ?string $title = 'Timeline Progres';

    protected static ?string $recordTitleAttribute = 'keterangan';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Data Progress')
                ->schema([
                    Grid::make(2)->schema([
                        DateTimePicker::make('waktu_progres')
                            ->label('Waktu Progres')
                            ->required(),

                        TextInput::make('persentase')
                            ->label('Persentase')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->required(),
                    ]),

                    Textarea::make('keterangan')
                        ->label('Keterangan')
                        ->rows(4)
                        ->required()
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('waktu_progres', 'desc')
            ->columns([
                TextColumn::make('waktu_progres')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('persentase')
                    ->label('Progress')
                    ->suffix('%')
                    ->badge()
                    ->sortable(),
                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}