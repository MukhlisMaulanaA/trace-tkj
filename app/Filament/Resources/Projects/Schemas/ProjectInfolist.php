<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Support\Enums\FontWeight;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class ProjectInfolist
{
  public static function configure(Schema $schema): Schema
  {
    return $schema
      ->components([

        // Section 1: Detail Utama Proyek
        Section::make('Informasi Proyek')
          ->description('Detail identitas dan lokasi proyek.')
          ->icon('heroicon-o-briefcase')
          ->columnSpanFull()
          ->columns([
            'default' => 3,
          ])
          ->schema([
            TextEntry::make('id')
              ->label('Project ID')
              ->weight(FontWeight::Bold)
              ->size(TextSize::Large)
              ->color('primary')
              ->copyable(),
            TextEntry::make('nama_project')
              ->label('Nama Proyek')
              ->weight(FontWeight::SemiBold),
            TextEntry::make('lokasi')
              ->label('Lokasi')
              ->icon('heroicon-o-map-pin') // Penambahan ikon visual
              ->placeholder('-'),
          ]),

        // Section 1: Detail Utama Proyek
        Section::make('Informasi Purchase Order')
          ->description('Detail identitas dan lokasi proyek.')
          ->icon('heroicon-o-briefcase')
          ->columnSpanFull()
          ->columns([
            'sm' => 2,
            'md' => 2,
          ])
          ->schema([
            // Tambahkan baris ini:
            TextEntry::make('purchaseOrders.po_number')
              ->label('Nomor PO Terkait')
              ->weight(FontWeight::Bold)
              ->size(TextSize::Medium)
              ->color('primary')
              ->copyable()
              ->placeholder('-')
              ->url(function ($record): ?string {
                $purchaseOrder = $record?->purchaseOrders?->first();

                if (!$purchaseOrder) {
                  return null;
                }

                return PurchaseOrderResource::getUrl('view', [
                  'record' => $purchaseOrder->getKey(),
                ]);
              })
              ->openUrlInNewTab()
              ->placeholder('Belum ada PO'),

            TextEntry::make('nomor_quotation')
              ->label('Nomor Quotation')
              ->copyable()
              ->placeholder('-'),
          ]),

        // Section 2: Pihak Terlibat
        Section::make('Entitas & Narahubung')
          ->description('Informasi kustomer dan penanggung jawab internal.')
          ->icon('heroicon-o-users')
          ->columnSpanFull()
          ->columns([
            'sm' => 1,
            'md' => 3, // Dibagi 3 agar berjajar rapi di desktop
          ])
          ->schema([
            TextEntry::make('kustomer')
              ->label('Kustomer')
              ->weight(FontWeight::Medium)
              ->icon('heroicon-o-building-office')
              ->placeholder('-'),
            TextEntry::make('kontak_person')
              ->label('Kontak Person')
              ->icon('heroicon-o-phone')
              ->placeholder('-'),
            TextEntry::make('pic')
              ->label('PIC Internal')
              ->icon('heroicon-o-user-circle')
              ->placeholder('-'),
          ]),

        // Section 3: Metadata Sistem
        Section::make('Informasi Sistem')
          ->icon('heroicon-o-clock')
          ->columnSpanFull()
          ->columns(2)
          ->collapsed() // Disembunyikan secara default agar UI tetap bersih
          ->schema([
            TextEntry::make('created_at')
              ->label('Dibuat Pada')
              ->dateTime()
              ->placeholder('-'),
            TextEntry::make('updated_at')
              ->label('Terakhir Diperbarui')
              ->dateTime()
              ->placeholder('-'),
          ]),
      ]);
  }
}