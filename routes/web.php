<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PurchaseOrderDocumentController;

Route::get('/', function () {
  return view('welcome');
});

Route::middleware(['auth'])->group(function () {
  Route::get(
    '/admin/purchase-orders/{purchaseOrder}/document',
    [PurchaseOrderDocumentController::class, 'show']
  )->name('purchase-orders.document');
});
Route::middleware(['auth'])->group(function () {
  Route::get(
    '/admin/purchase-orders/{purchaseOrder}/document',
    [PurchaseOrderDocumentController::class, 'show']
  )->name('purchase-orders.document');
});
