<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPurchaseOrder extends ViewRecord
{
  protected static string $resource = PurchaseOrderResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Action::make('document')
        ->label('Document PO')
        ->icon('heroicon-o-document-text')
        ->color('success')
        ->url(
          fn() => route(
            'purchase-orders.document',
            [
              'purchaseOrder' => $this->record,
            ]
          )
        )
        ->openUrlInNewTab(),

      EditAction::make(),
    ];
  }
}
