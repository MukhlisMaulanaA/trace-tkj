<?php

use App\Http\Controllers\PurchaseOrderDocumentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
  return view('welcome');
});

Route::middleware(['auth'])->group(function () {
  Route::get(
    '/admin/purchase-orders/{purchaseOrder}/document',
    [PurchaseOrderDocumentController::class, 'show']
  )->name('purchase-orders.document');
});