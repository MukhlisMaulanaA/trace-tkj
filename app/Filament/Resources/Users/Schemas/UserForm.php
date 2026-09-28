<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;

class UserForm
{
  public static function configure(Schema $schema): Schema
  {
    return $schema
      ->columns(2)
      ->components([
        TextInput::make('name')
          ->label('Nama Lengkap')
          ->placeholder('Masukkan nama lengkap')
          ->required()
          ->maxLength(255),

        TextInput::make('email')
          ->label('Alamat Email')
          ->placeholder('nama@domain.com')
          ->email()
          ->required()
          ->maxLength(255)
          ->unique(ignoreRecord: true),

        Select::make('project_scope')
          ->label('Cakupan Proyek')
          ->options([
            'pusat' => 'Pusat',
            'distrik_8' => 'Distrik 8',
          ])
          ->default('pusat')
          ->required()
          ->native(false)
          ->columnSpanFull(),

        DateTimePicker::make('email_verified_at')
          ->label('Waktu Verifikasi Email')
          ->placeholder('Pilih tanggal & waktu')
          ->columnSpanFull(),

        TextInput::make('password')
          ->password()
          ->default('Trace_TKJ123')
          ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
          ->dehydrated(fn(?string $state): bool => filled($state))
          ->hidden(),
      ]);
  }
}