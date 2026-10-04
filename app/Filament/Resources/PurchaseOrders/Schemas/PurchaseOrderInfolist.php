<?php

namespace App\Filament\Resources\PurchaseOrders\Schemas;

use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\FontWeight;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\Projects\ProjectResource;


class PurchaseOrderInfolist
{
  public static function configure(Schema $schema): Schema
  {
    return $schema
      ->components([

        /*
        |--------------------------------------------------------------------------
        | INFORMASI UTAMA
        |--------------------------------------------------------------------------
        */

        Section::make('Informasi Utama')
          ->description('Detail dasar Purchase Order')
          ->icon('heroicon-o-document-text')
          ->columnSpanFull() // <-- Method untuk membuat section full width
          ->columns([
            'default' => 3,
          ])
          ->schema([

            TextEntry::make('po_number')
              ->label('PO Number')
              ->weight(FontWeight::Bold)
              ->size(TextSize::Large)
              ->copyable()
              ->copyMessage('PO Number disalin!')
              ->copyMessageDuration(1500)
              ->color('primary')
              ->placeholder('-'),

            TextEntry::make('po_date')
              ->label('PO Date')
              ->date('d M Y')
              ->placeholder('-'),

            TextEntry::make('type')
              ->label('Type')
              ->badge()
              ->formatStateUsing(
                fn(?string $state): string =>
                  match ($state) {
                    'project' => 'Project',
                    'vendor' => 'Vendor',
                    default => filled($state)
                      ? ucfirst($state)
                      : '-',
                  }
              )
              ->color(
                fn(?string $state): string =>
                  match ($state) {
                    'project' => 'primary',
                    'vendor' => 'warning',
                    default => 'gray',
                  }
              ),
          ]),


        /*
        |--------------------------------------------------------------------------
        | PROJECT
        |--------------------------------------------------------------------------
        |
        | Hanya muncul untuk PO type = project.
        |
        */

        Section::make('Project')
          ->description('Project yang terkait dengan Purchase Order ini.')
          ->icon('heroicon-o-building-office-2')
          ->visible(
            fn($record): bool =>
              $record->type === 'project'
          )
          ->columns([
            'default' => 1,
            'sm' => 2,
          ])
          ->schema([

            TextEntry::make('project.id')
              ->label('Project ID')
              ->weight(FontWeight::Bold)
              ->size(TextSize::Medium)
              ->color('primary')
              ->copyable()
              ->placeholder('-')
              ->url(
                fn($record): ?string => $record->project_id
                  ? ProjectResource::getUrl('view', ['record' => $record->project_id])
                  : null
              )
              ->openUrlInNewTab(), // Opsional: Sangat disarankan agar pengguna tidak kehilangan halaman Infolist saat ini

            TextEntry::make('project.nama_project')
              ->label('Nama Project')
              ->weight(FontWeight::SemiBold)
              ->placeholder('-'),
          ]),


        /*
        |--------------------------------------------------------------------------
        | VENDOR
        |--------------------------------------------------------------------------
        |
        | Hanya muncul untuk PO type = vendor.
        |
        */

        Section::make('Vendor')
          ->description('Informasi vendor untuk Purchase Order ini.')
          ->icon('heroicon-o-building-storefront')
          ->visible(
            fn($record): bool =>
              $record->type === 'vendor'
          )
          ->columns([
            'default' => 1,
            'sm' => 2,
            'lg' => 3,
          ])
          ->schema([

            TextEntry::make('vendor.name')
              ->label('Vendor')
              ->weight(FontWeight::Bold)
              ->color('primary')
              ->placeholder('-'),

            TextEntry::make('customer')
              ->label('Customer / Pemesan')
              ->placeholder('-'),

            TextEntry::make('quotation_no')
              ->label('Reference / Quotation')
              ->placeholder('-')
              ->copyable(),
          ]),


        /*
        |--------------------------------------------------------------------------
        | INFORMASI PROJECT
        |--------------------------------------------------------------------------
        |
        | Tidak ditampilkan untuk Vendor PO.
        |
        */

        Section::make('Informasi Project')
          ->description(
            'Informasi transaksi yang berkaitan dengan project.'
          )
          ->icon('heroicon-o-briefcase')
          ->visible(
            fn($record): bool =>
              $record->type === 'project'
          )
          ->columns([
            'default' => 1,
            'sm' => 2,
            'lg' => 4,
          ])
          ->schema([

            TextEntry::make('customer')
              ->label('Customer')
              ->placeholder('-')
              ->weight(FontWeight::SemiBold),

            TextEntry::make('quotation_no')
              ->label('Quotation No.')
              ->placeholder('-')
              ->copyable(),

            TextEntry::make('pic')
              ->label('PIC')
              ->placeholder('-'),

            TextEntry::make('location')
              ->label('Location')
              ->placeholder('-')
              ->columnSpan([
                'default' => 1,
                'sm' => 2,
                'lg' => 1,
              ]),
          ]),


        /*
        |--------------------------------------------------------------------------
        | RINGKASAN PURCHASE ORDER
        |--------------------------------------------------------------------------
        |
        | Dibuat card-like agar konsisten dengan Project Summary.
        |
        */

        Section::make('Ringkasan Purchase Order')
          ->description(
            'Ringkasan nilai dan perhitungan Purchase Order.'
          )
          ->icon('heroicon-o-banknotes')
          ->collapsed()
          ->schema([

            Grid::make([
              'default' => 1,
              'sm' => 1,
              'lg' => 1,
            ])
              ->schema([

                TextEntry::make('subtotal')
                  ->label('Subtotal')
                  ->money('IDR', 0)
                  ->weight(FontWeight::SemiBold),

                TextEntry::make('discount_amount')
                  ->label('Diskon')
                  ->money('IDR', 0)
                  ->placeholder('Rp 0')
                  ->visible(
                    fn($record): bool =>
                      (bool) $record->discount_enabled
                  ),

                TextEntry::make('subtotal_after_discount')
                  ->label('Subtotal Setelah Diskon')
                  ->state(
                    fn($record): float =>
                      max(
                        0,
                        (float) ($record->subtotal ?? 0)
                        - (float) ($record->discount_amount ?? 0)
                      )
                  )
                  ->money('IDR', 0),

                TextEntry::make('dpp_amount')
                  ->label('DPP')
                  ->money('IDR', 0)
                  ->visible(
                    fn($record): bool =>
                      (bool) $record->dpp_enabled
                  ),

                TextEntry::make('ppn_amount')
                  ->label(
                    fn($record): string =>
                      'PPN ' .
                      (
                        $record->dpp_enabled
                        ? '12%'
                        : '11%'
                      )
                  )
                  ->money('IDR', 0)
                  ->visible(
                    fn($record): bool =>
                      (bool) $record->ppn_enabled
                  ),

                TextEntry::make('grand_total')
                  ->label('Grand Total')
                  ->money('IDR', 0)
                  ->weight(FontWeight::Bold)
                  ->size(TextSize::Large)
                  ->color('primary')
                  ->columnSpan([
                    'default' => 1,
                  ]),
              ]),
          ]),


        /*
        |--------------------------------------------------------------------------
        | CATATAN
        |--------------------------------------------------------------------------
        */

        Section::make('Catatan')
          ->icon('heroicon-o-clipboard-document-list')
          ->schema([

            TextEntry::make('notes')
              ->hiddenLabel()
              ->placeholder('Tidak ada catatan.')
              ->prose()
              ->columnSpanFull(),
          ]),


        /*
        |--------------------------------------------------------------------------
        | INFORMASI SISTEM
        |--------------------------------------------------------------------------
        */

        Section::make('Informasi Sistem')
          ->icon('heroicon-o-clock')
          ->columns([
            'default' => 1,
            'sm' => 2,
          ])
          ->collapsed()
          ->schema([

            TextEntry::make('created_at')
              ->label('Dibuat Pada')
              ->dateTime('d M Y, H:i')
              ->placeholder('-'),

            TextEntry::make('updated_at')
              ->label('Terakhir Diperbarui')
              ->dateTime('d M Y, H:i')
              ->placeholder('-'),
          ]),
      ]);
  }
}