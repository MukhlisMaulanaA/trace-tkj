<?php

namespace App\Filament\Resources\PurchaseOrders\Schemas;

use App\Models\Project;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Toggle;

class PurchaseOrderForm
{
  public static function configure(Schema $schema): Schema
  {
    return $schema
      ->components([
        /*
        |--------------------------------------------------------------------------
        | INFORMASI PURCHASE ORDER
        |--------------------------------------------------------------------------
        */

        Section::make('Informasi Purchase Order')
          ->description('Identitas utama Purchase Order')
          ->icon('heroicon-o-document-text')
          ->schema([
            Grid::make([
              'default' => 1,
              'md' => 2,
            ])
              ->schema([
                TextInput::make('po_code')
                  ->label('ID PO')
                  ->disabled()
                  ->dehydrated(false)
                  ->placeholder('Otomatis')
                  ->helperText('ID PO dibuat otomatis oleh sistem.'),

                TextInput::make('po_number')
                  ->label('Nomor PO')
                  ->required()
                  ->unique(ignoreRecord: true)
                  ->placeholder('Masukkan nomor PO sesuai dokumen/client')
                  ->helperText(
                    'Format nomor PO bebas sesuai kebutuhan project/client.'
                  ),

                DatePicker::make('po_date')
                  ->label('Tanggal PO')
                  ->default(now())
                  ->required()
                  ->native(false),
              ]),
          ]),

        /*
        |--------------------------------------------------------------------------
        | PROJECT
        |--------------------------------------------------------------------------
        */

        Section::make('Referensi Project')
          ->description('Pilih project yang menjadi referensi Purchase Order')
          ->icon('heroicon-o-briefcase')
          ->schema([
            Select::make('project_id')
              ->label('Project')
              ->relationship(
                name: 'project',
                titleAttribute: 'id',
              )
              ->getOptionLabelFromRecordUsing(
                fn(Project $record): string =>
                "{$record->id} — {$record->nama_project}"
              )
              ->searchable([
                'id',
                'nama_project',
                'kustomer',
                'lokasi',
              ])
              ->preload()
              ->live()
              ->required()
              ->placeholder('Pilih project')
              ->afterStateUpdated(function (?string $state, Set $set): void {
                if (blank($state)) {
                  $set('customer', null);
                  $set('location', null);
                  $set('quotation_no', null);
                  $set('pic', null);

                  return;
                }

                $project = Project::find($state);

                if (!$project) {
                  return;
                }

                $set('customer', $project->kustomer);
                $set('location', $project->lokasi);
                $set('quotation_no', $project->nomor_quotation);
                $set('pic', $project->pic);
              })
              ->columnSpanFull(),

            Grid::make([
              'default' => 1,
              'md' => 2,
            ])
              ->schema([
                TextInput::make('customer')
                  ->label('Customer')
                  ->required()
                  ->placeholder('Otomatis dari project')
                  ->helperText(
                    'Otomatis diambil dari project dan masih dapat disesuaikan.'
                  ),

                TextInput::make('location')
                  ->label('Lokasi')
                  ->required()
                  ->placeholder('Otomatis dari project')
                  ->helperText(
                    'Otomatis diambil dari project dan masih dapat disesuaikan.'
                  ),

                TextInput::make('quotation_no')
                  ->label('Nomor Quotation')
                  ->placeholder('Otomatis dari project')
                  ->helperText(
                    'Otomatis diambil dari project dan masih dapat disesuaikan.'
                  ),

                TextInput::make('pic')
                  ->label('PIC / Penanggung Jawab')
                  ->required()
                  ->placeholder('Otomatis dari project')
                  ->helperText(
                    'Otomatis diambil dari project dan masih dapat disesuaikan.'
                  ),
              ]),
          ]),

        /*
        |--------------------------------------------------------------------------
        | PAJAK
        |--------------------------------------------------------------------------
        */

        // Section::make('Pajak')
        //   ->description('Pengaturan pajak Purchase Order')
        //   ->icon('heroicon-o-receipt-percent')
        //   ->schema([
        //     \Filament\Forms\Components\Toggle::make('ppn_enabled')
        //       ->label('Terapkan PPN')
        //       ->helperText(
        //         'PPN ditampilkan sebagai 12%, tetapi perhitungan menggunakan 11% dari subtotal.'
        //       )
        //       ->default(false)
        //       ->live(),
        //   ])
        //   ->collapsible(),

        Section::make('Perhitungan Purchase Order')
          ->description('Pengaturan pajak dan diskon Purchase Order')
          ->icon('heroicon-o-calculator')
          ->schema([
            Grid::make([
              'default' => 1,
              'md' => 2,
            ])
              ->schema([

                /*
                |--------------------------------------------------------------------------
                | PPN
                |--------------------------------------------------------------------------
                */

                Toggle::make('ppn_enabled')
                  ->label('Aktifkan PPN')
                  ->default(false)
                  ->live(),

                TextInput::make('ppn_display')
                  ->label('PPN')
                  ->default('12%')
                  ->disabled()
                  ->dehydrated(false)
                  ->helperText(
                    'Ditampilkan 12%, tetapi kalkulasi menggunakan 11%.'
                  ),

                /*
                |--------------------------------------------------------------------------
                | DISCOUNT
                |--------------------------------------------------------------------------
                */

                Toggle::make('discount_enabled')
                  ->label('Aktifkan Diskon')
                  ->default(false)
                  ->live()
                  ->columnSpanFull(),

                TextInput::make('discount_percent')
                  ->label('Diskon')
                  ->suffix('%')
                  ->numeric()
                  ->minValue(0)
                  ->maxValue(100)
                  ->default(0)
                  ->visible(
                    fn($get): bool =>
                    (bool) $get('discount_enabled')
                  )
                  ->required(
                    fn($get): bool =>
                    (bool) $get('discount_enabled')
                  )
                  ->helperText(
                    'Diskon dihitung dari total setelah PPN.'
                  ),

              ]),
          ])
          ->columns(1),


        /*
        |--------------------------------------------------------------------------
        | CATATAN
        |--------------------------------------------------------------------------
        */

        Section::make('Catatan')
          ->description('Informasi tambahan untuk Purchase Order')
          ->icon('heroicon-o-chat-bubble-left-right')
          ->schema([
            Textarea::make('notes')
              ->label('Notes')
              ->placeholder('Tambahkan catatan jika diperlukan...')
              ->rows(4)
              ->autosize()
              ->columnSpanFull(),
          ]),

        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        TextInput::make('status')
          ->default('draft')
          ->hidden()
          ->dehydrated(true),
      ]);
  }
}