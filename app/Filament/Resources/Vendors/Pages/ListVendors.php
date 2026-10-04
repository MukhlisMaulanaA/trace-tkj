<?php

namespace App\Filament\Resources\Vendors\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\Vendors\VendorResource;

class ListVendors extends ListRecords
{
  protected static string $resource = VendorResource::class;

  protected function getHeaderActions(): array
  {
    return [
      CreateAction::make(),
    ];
  }
}
