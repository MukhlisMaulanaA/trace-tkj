<?php

namespace App\Filament\Resources\Vendors\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VendorForm
{
  public static function configure(Schema $schema): Schema
  {
    return $schema->components([
      Section::make('Informasi Vendor')
        ->schema([
          TextInput::make('name')
            ->label('Nama Vendor')
            ->placeholder('Masukan nama vendor')
            ->required()
            ->unique(ignoreRecord: true)
            ->maxLength(255),
          Grid::make(2)->schema([
            TextInput::make('contact_person')
            ->label('Contact Person')
            ->placeholder('Orang yang dihubungi')
            ->maxLength(255),
            TextInput::make('phone')
            ->label('Telepon')
            ->placeholder('Nomor HP/Whatsapp')
            ->maxLength(255),
            TextInput::make('email')
            ->email()
            ->placeholder('Email bila diperlukan')
            ->maxLength(255),
          ]),
          Textarea::make('address')
          ->label('Alamat')
          ->placeholder('Lokasi/Asal Vendor')
          ->rows(3)
          ->columnSpanFull(),
        ]),
    ]);
  }
}
