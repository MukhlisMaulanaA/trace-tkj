<?php

namespace App\Filament\Resources\Vendors\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class VendorInfolist
{
  public static function configure(Schema $schema): Schema
  {
    return $schema->components([
      TextEntry::make('name')->label('Nama Vendor'),
      TextEntry::make('contact_person')->label('Contact Person')->placeholder('-'),
      TextEntry::make('phone')->label('Telepon')->placeholder('-'),
      TextEntry::make('email')->placeholder('-'),
      TextEntry::make('address')->label('Alamat')->placeholder('-')->columnSpanFull(),
      TextEntry::make('created_at')->dateTime()->placeholder('-'),
      TextEntry::make('updated_at')->dateTime()->placeholder('-'),
    ]);
  }
}
