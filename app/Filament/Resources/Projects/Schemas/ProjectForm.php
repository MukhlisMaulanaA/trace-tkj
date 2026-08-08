<?php

namespace App\Filament\Resources\Projects\Schemas;

// use Filament\Forms\Components\Grid;
// use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProjectForm
{
  public static function configure(Schema $schema): Schema
  {
    return $schema
      ->components([
        Section::make('Informasi Utama Project')
          ->description('Detail identitas dan lokasi pelaksanaan project')
          ->icon('heroicon-o-briefcase')
          ->schema([
            // Grid 2 kolom untuk tampilan desktop
            Grid::make(2)
              ->schema([
                TextInput::make('id')
                  ->label('ID Project')
                  ->placeholder('Otomatis di-generate (Contoh: P26H001)')
                  ->disabled()
                  ->dehydrated(false) // Mencegah data disabled terikutsertakan saat disave
                  ->visible(fn($record) => $record !== null), // Hanya muncul saat edit/view, tersembunyi saat create baru

                TextInput::make('pic')
                  ->label('PIC/Penanggung Jawab')
                  ->placeholder('Masukan nama PIC')
                  ->required()
                  ->maxLength(255),

                TextInput::make('nomor_quotation')
                  ->label('Nomor Quotation')
                  ->placeholder('Masukkan nomor quotation')
                  ->required()
                  ->maxLength(255),
              ]),

            TextInput::make('nama_project')
              ->label('Nama Project')
              ->placeholder('Masukkan nama project')
              ->required()
              ->maxLength(255)
              ->columnSpanFull(),

            Grid::make(2)
              ->schema([
                TextInput::make('kustomer')
                  ->label('Kustomer')
                  ->placeholder('Nama kustomer / perusahaan')
                  ->required()
                  ->maxLength(255),

                TextInput::make('lokasi')
                  ->label('Lokasi Project')
                  ->placeholder('Lokasi atau area pelaksanaan')
                  ->required()
                  ->maxLength(255),
              ]),
          ]),
      ]);
  }
}