<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use Illuminate\View\View;

class PurchaseOrderDocumentController extends Controller
{
  public function show(PurchaseOrder $purchaseOrder): View
  {
    $purchaseOrder->load([
      'project',
      'items',
    ]);

    return view(
      'purchase-orders.document',
      [
        'purchaseOrder' => $purchaseOrder,
      ]
    );
  }
}