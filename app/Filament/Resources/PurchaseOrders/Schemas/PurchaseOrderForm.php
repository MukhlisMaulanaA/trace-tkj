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
                TextInput::make('po_number')
                  ->label('Nomor PO')
                  ->placeholder('Otomatis saat PO dibuat')
                  ->disabled()
                  ->dehydrated()
                  ->unique(ignoreRecord: true),

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