<?php

namespace App\Filament\Resources\PurchaseOrders\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderProgressRelationManager extends RelationManager
{
  protected static string $relationship = 'progresses';

  protected static ?string $title = 'Invoice Progress';

  protected static ?string $recordTitleAttribute = 'title';

  /**
   * Progress dapat dikelola langsung dari halaman View PurchaseOrder.
   */
  public function isReadOnly(): bool
  {
    return false;
  }

  public function form(Schema $schema): Schema
  {
    return $schema->components([
      Section::make('Invoice Details')
        ->schema([
          Grid::make(2)->schema([
            TextInput::make('title')
              ->label('Invoice Title/Number')
              ->placeholder('INV-001, DOC-2026-001, etc.')
              ->required(),

            DateTimePicker::make('invoice_date')
              ->label('Invoice Date')
              ->required(),
          ]),

          Grid::make(2)->schema([
            TextInput::make('amount')
              ->label('Invoice Amount')
              ->numeric()
              ->minValue(0)
              ->prefix('Rp ')
              ->step(0.01)
              ->required(),

            TextInput::make('percentage')
              ->label('Progress Percentage')
              ->numeric()
              ->minValue(0)
              ->maxValue(100)
              ->suffix('%')
              ->disabled()
              ->helperText('Calculated automatically based on PO grand total'),
          ]),

          FileUpload::make('pdf_file')
            ->label('PDF Document')
            ->disk('public')
            ->directory('invoices')
            ->acceptedFileTypes(['application/pdf'])
            ->maxSize(5 * 1024)
            ->helperText('Upload PDF invoice (max 5MB)')
            ->columnSpanFull(),
        ]),
    ]);
  }

  public function table(Table $table): Table
  {
    return $table
      ->heading('Invoice Progress Timeline')

      /*
       * Header custom:
       * menampilkan progress terkini + progress bar.
       */
      ->header(view(
        'filament.tables.headers.po-progress-header',
        [
          'purchaseOrder' => $this->getOwnerRecord(),
        ],
      ))

      /*
       * Timeline harus chronological:
       * Invoice terbaru di bawah.
       */
      ->defaultSort('invoice_date', 'asc')

      ->paginated(false)

      ->striped(false)

      ->columns([
        ViewColumn::make('timeline')
          ->label('')
          ->view('filament.tables.columns.po-progress-timeline'),
      ])

      ->headerActions([
        CreateAction::make()
          ->label('Tambah Invoice')
          ->mutateFormDataUsing(function (array $data): array {
            $data['is_system'] = false;
            
            // Remove percentage from form data to let model calculate it
            unset($data['percentage']);

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
