<?php

namespace App\Filament\Resources\Vendors\Schemas;

use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Support\Enums\FontWeight;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class VendorInfolist
{
  public static function configure(Schema $schema): Schema
  {
    return $schema
      ->components([

        // Bagian 1: Profil Utama Vendor
        Section::make('Profil Vendor')
          ->description('Informasi identitas dan lokasi perusahaan.')
          ->icon('heroicon-o-building-storefront')
          ->columnSpanFull()
          ->columns([
            'default' => 2,
            'sm' => 1,
            'md' => 2,
          ])
          ->schema([
            TextEntry::make('name')
              ->label('Nama Vendor')
              ->weight(FontWeight::Bold)
              ->size(TextSize::Large)
              ->color('primary')
              ->copyable(),
            TextEntry::make('address')
              ->label('Alamat Lengkap')
              ->icon('heroicon-o-map-pin')
              ->placeholder('-')
          ]),

        // Bagian 2: Kontak & Komunikasi
        Section::make('Informasi Kontak')
          ->description('Detail narahubung yang dapat dihubungi.')
          ->icon('heroicon-o-identification')
          ->columnSpanFull()
          ->columns([
            'sm' => 1,
            'md' => 3,
          ])
          ->schema([
            TextEntry::make('contact_person')
              ->label('Contact Person')
              ->icon('heroicon-o-user')
              ->weight(FontWeight::Medium)
              ->placeholder('-'),

            TextEntry::make('phone')
              ->label('Telepon')
              ->icon('heroicon-o-phone')
              ->color('primary')
              ->copyable()
              // Fitur UX: Klik nomor akan otomatis membuka aplikasi telepon (tel:)
              ->url(function (?string $state): ?string {
                if (!$state) {
                  return null;
                }

                // 1. Hapus semua karakter selain angka (spasi, tanda +, strip, dll)
                $cleanNumber = preg_replace('/[^0-9]/', '', $state);

                // 2. Jika nomor diawali dengan angka '0', ubah menjadi '62'
                if (str_starts_with($cleanNumber, '0')) {
                  $cleanNumber = '62' . substr($cleanNumber, 1);
                }

                return "https://wa.me/{$cleanNumber}";
              })
              ->openUrlInNewTab()
              ->placeholder('-'),

            TextEntry::make('email')
              ->label('Email')
              ->icon('heroicon-o-envelope')
              ->copyable()
              // Fitur UX: Klik email akan otomatis membuka aplikasi email (mailto:)
              ->url(fn(?string $state): ?string => $state ? "mailto:{$state}" : null)
              ->placeholder('-'),
          ]),

        // Bagian 3: Metadata Sistem
        Section::make('Informasi Sistem')
          ->icon('heroicon-o-clock')
          ->columns(2)
          ->collapsed() // Disembunyikan secara default
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