<?php

use App\Http\Controllers\PurchaseOrderDocumentController;
use Illuminate\Support\Facades\Route;

// Redirect route utama langsung ke Dashboard Filament Admin
Route::get('/', function () {
  return redirect('/admin');
});

Route::middleware(['auth'])->group(function () {
  Route::get(
    '/admin/purchase-orders/{purchaseOrder}/document',
    [PurchaseOrderDocumentController::class, 'show']
  )->name('purchase-orders.document');
});