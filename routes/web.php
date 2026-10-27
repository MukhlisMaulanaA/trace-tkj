<?php

use App\Http\Controllers\InvoiceDocumentController;
use App\Http\Controllers\ProjectSummaryDocumentController;
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

    Route::get(
        '/admin/project-summaries/{projectSummary}/document',
        [ProjectSummaryDocumentController::class, 'show']
    )->name('project-summaries.document');

    Route::get('/admin/invoices/{invoice}/document', [InvoiceDocumentController::class, 'invoice'])
        ->name('invoices.document');

    Route::get('/admin/invoices/{invoice}/receipt', [InvoiceDocumentController::class, 'receipt'])
        ->name('invoices.receipt');

    Route::get('/admin/invoices/{invoice}/excel', [InvoiceDocumentController::class, 'excel'])
        ->name('invoices.excel');

    Route::get('/admin/invoices-summary/yearly/{year?}', [InvoiceDocumentController::class, 'yearlySummary'])
        ->whereNumber('year')
        ->name('invoices.yearly-summary');
});
