<?php

namespace App\Filament\Resources\PurchaseOrders\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
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

  /**
   * Ambil Purchase Order pemilik relation ini.
   */
  protected function getPurchaseOrder(): Model
  {
    return $this->getOwnerRecord();
  }

  /**
   * Konversi nilai amount dari UI ke angka murni.
   *
   * Contoh:
   * 43,250,000 -> 43250000
   */
  protected function parseAmount(mixed $value): float
  {
    if ($value === null || $value === '') {
      return 0;
    }

    return (float) str_replace(',', '', (string) $value);
  }

  /**
   * Format Rupiah tanpa pecahan sen.
   */
  protected function formatRupiah(float|int $amount): string
  {
    return 'Rp ' . number_format(
      $amount,
      0,
      ',',
      '.'
    );
  }

  /**
   * Hitung total invoice yang sudah tercatat.
   *
   * Saat edit invoice, invoice yang sedang diedit
   * dikeluarkan dari perhitungan.
   */
  protected function getTotalExistingInvoices(?Model $record = null): float
  {
    $query = $this->getPurchaseOrder()
      ->progresses();

    if ($record?->exists) {
      $query->whereKeyNot($record->getKey());
    }

    return (float) $query->sum('amount');
  }

  /**
   * Hitung sisa tagihan sebelum invoice baru.
   */
  protected function getRemainingBeforeCurrentInvoice(?Model $record = null): float
  {
    $grandTotal = (float) ($this->getPurchaseOrder()->grand_total ?? 0);

    $existingInvoices = $this->getTotalExistingInvoices($record);

    return max(
      0,
      $grandTotal - $existingInvoices
    );
  }

  public function form(Schema $schema): Schema
  {
    $purchaseOrder = $this->getPurchaseOrder();

    return $schema->components([
      Section::make('Invoice Details')
        ->schema([

          Grid::make(2)
            ->schema([
              TextInput::make('title')
                ->label('Invoice Title/Number')
                ->placeholder('INV-001, DOC-2026-001, etc.')
                ->required(),

              DateTimePicker::make('invoice_date')
                ->label('Invoice Date')
                ->required(),
            ]),

          Grid::make(2)
            ->schema([

              TextInput::make('amount')
                ->label('Invoice Amount')
                ->prefix('Rp')
                ->required()
                ->live(onBlur: true)
                ->mask(
                  RawJs::make('$money($input)')
                )
                ->stripCharacters(',')
                ->numeric()
                ->live(onBlur: true)
                /*
                 * Hitung progress otomatis ketika
                 * user mengubah nilai invoice.
                 */
                ->afterStateUpdated(
                  function (mixed $state, Set $set) use ($purchaseOrder): void {

                    $amount = $this->parseAmount($state);

                    $grandTotal = (float) (
                      $purchaseOrder->grand_total ?? 0
                    );

                    if ($grandTotal <= 0) {
                      $set('percentage', 0);

                      return;
                    }

                    $percentage = round(
                      ($amount / $grandTotal) * 100,
                      2
                    );

                    $set(
                      'percentage',
                      min(100, $percentage)
                    );
                  }
                ),

                /*
                 * Invoice tidak boleh melebihi
                 * sisa tagihan. (tidak digunakan)
                 */
                // ->rules([
                //   function (?Model $record) {
                //     return function (string $attribute, mixed $value, \Closure $fail) use ($record): void {

                //       $amount = $this->parseAmount($value);

                //       $remaining =
                //         $this->getRemainingBeforeCurrentInvoice(
                //           $record
                //         );

                //       if ($amount > $remaining) {
                //         $fail(
                //           'Invoice tidak boleh melebihi '
                //           . $this->formatRupiah($remaining)
                //           . '.'
                //         );
                //       }
                //     };
                //   },
                // ]),

              TextInput::make('percentage')
                ->label('Progress Percentage')
                ->numeric()
                ->minValue(0)
                ->maxValue(100)
                ->suffix('%')
                ->disabled()
                ->dehydrated(false)
                ->helperText(
                  'Calculated automatically based on PO grand total'
                ),
            ]),

          /*
           * INFORMASI TAGIHAN
           */
          Grid::make(2)
            ->schema([

              Placeholder::make('po_grand_total')
                ->label('PO Grand Total')
                ->content(
                  fn(): string => $this->formatRupiah(
                    (float) (
                      $purchaseOrder->grand_total ?? 0
                    )
                  )
                ),

              Placeholder::make('remaining_amount')
                ->label('Sisa Tagihan')
                ->content(
                  function (Get $get, ?Model $record): string {

                    $remaining =
                      $this->getRemainingBeforeCurrentInvoice(
                        $record
                      );

                    $currentAmount =
                      $this->parseAmount(
                        $get('amount')
                      );

                    /*
                     * Sisa setelah invoice yang sedang
                     * diinput.
                     */
                    $remainingAfterCurrent =
                      max(
                        0,
                        $remaining - $currentAmount
                      );

                    return $this->formatRupiah(
                      $remainingAfterCurrent
                    );
                  }
                ),
            ]),

          FileUpload::make('pdf_file')
            ->label('PDF Document')
            ->disk('public')
            ->directory('invoices')
            ->acceptedFileTypes([
              'application/pdf'
            ])
            ->maxSize(5 * 1024)
            ->helperText(
              'Upload PDF invoice (max 5MB)'
            )
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
      ->header(
        view(
          'filament.tables.headers.po-progress-header',
          [
            'purchaseOrder' => $this->getOwnerRecord(),
          ]
        )
      )

      /*
       * Timeline chronological:
       * Invoice terbaru di bawah.
       */
      ->defaultSort('invoice_date', 'asc')

      ->paginated(false)

      ->striped(false)

      ->columns([
        ViewColumn::make('timeline')
          ->label('')
          ->view(
            'filament.tables.columns.po-progress-timeline'
          ),
      ])

      ->headerActions([
        CreateAction::make()
          ->label('Tambah Invoice')

          ->mutateFormDataUsing(
            function (array $data): array {

              $data['is_system'] = false;

              /*
               * Percentage dihitung oleh aplikasi.
               */
              unset($data['percentage']);

              return $data;
            }
          ),
      ])

      ->recordActions([
        EditAction::make()
          ->label('Update')
          ->visible(
            fn(Model $record): bool =>
            !$record->is_system
          ),

        DeleteAction::make()
          ->label('Delete')
          ->visible(
            fn(Model $record): bool =>
            !$record->is_system
          ),
      ]);
  }
}