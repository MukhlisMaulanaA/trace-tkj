<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoice extends ViewRecord
{
  protected static string $resource = InvoiceResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Action::make('invoicePdf')
        ->label('Preview Invoice')
        ->icon('heroicon-o-eye')
        ->url(fn(Invoice $record): string => route('invoices.document', $record))
        ->openUrlInNewTab(),
      Action::make('receiptPdf')
        ->label('Preview Receipt')
        ->icon('heroicon-o-eye')
        ->url(fn(Invoice $record): string => route('invoices.receipt', $record))
        ->openUrlInNewTab(),
      EditAction::make(),
    ];
  }
}