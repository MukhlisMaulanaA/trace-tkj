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
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProjectProgressRelationManager extends RelationManager
{
  protected static string $relationship = 'progresses';

  protected static ?string $title = 'Timeline Progres';

  protected static ?string $recordTitleAttribute = 'keterangan';

  /**
   * Progress dapat dikelola langsung dari halaman View Project.
   */
  public function isReadOnly(): bool
  {
    return false;
  }

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
      ->heading('Timeline Progres')

      /*
       * Header custom:
       * menampilkan progress terkini + progress bar.
       */
      ->header(view(
        'filament.tables.headers.project-progress-header',
        [
          'project' => $this->getOwnerRecord(),
        ],
      ))

      /*
       * Timeline harus chronological:
       * Project Created -> progress lama -> progress terbaru.
       */
      ->defaultSort('waktu_progres', 'asc')

      ->paginated(false)

      ->striped(false)

      ->columns([
        ViewColumn::make('timeline')
          ->label('')
          ->view('filament.tables.columns.project-progress-timeline'),
      ])

      ->headerActions([
        CreateAction::make()
          ->label('Tambah Progress')
          ->mutateFormDataUsing(function (array $data): array {
            $data['is_system'] = false;

            return $data;
          }),
      ])

      ->recordActions([
        EditAction::make()
          ->label('Update')
          ->visible(
            fn(Model $record): bool => !$record->is_system
          ),

        DeleteAction::make()
          ->label('Delete')
          ->visible(
            fn(Model $record): bool => !$record->is_system
          ),
      ]);
  }
}