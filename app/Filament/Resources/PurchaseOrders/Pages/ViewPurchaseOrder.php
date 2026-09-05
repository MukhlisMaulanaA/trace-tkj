<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

class ViewPurchaseOrder extends ViewRecord
{
  protected static string $resource = PurchaseOrderResource::class;

  protected function getHeaderActions(): array
  {
    return [

      Action::make('document')
        ->label('Dokumen PO')
        ->icon('heroicon-o-document-text')
        ->color('success')
        ->url(
          fn(PurchaseOrder $record): string =>
          route(
            'purchase-orders.document',
            $record
          )
        )
        ->openUrlInNewTab(),

      Action::make('previewPdf')
        ->label('PO Client')
        ->icon('heroicon-o-document-text')
        ->color('gray')
        ->url(fn($record): string => Storage::disk('public')->url($record->pdf_file))
        ->openUrlInNewTab()
        ->visible(fn($record): bool => filled($record->pdf_file)),

      EditAction::make(),

    ];
  }
}