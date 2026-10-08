<?php

namespace App\Filament\Resources\Invoices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvoicesTable
{
  public static function configure(Table $table): Table
  {
    return $table->columns([
      TextColumn::make('invoice_code')->label('Invoice ID')->searchable()->sortable(),
      TextColumn::make('recipient')->label('To')->searchable(),
      TextColumn::make('purchaseOrder.po_number')->label('PO Number')->searchable(),
      TextColumn::make('project_name')->searchable(),
      TextColumn::make('invoice_date')->date()->sortable(),
      TextColumn::make('payment_type')->formatStateUsing(fn(string $state): string => ucwords(str_replace('_', ' ', $state)))->badge(),
      TextColumn::make('grand_total')->money('IDR')->sortable(),
      TextColumn::make('status')->badge(),
    ])->recordActions([
      ViewAction::make(),
      EditAction::make(),
    ])->toolbarActions([
      BulkActionGroup::make([DeleteBulkAction::make()]),
    ]);
  }
}