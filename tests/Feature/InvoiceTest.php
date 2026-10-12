<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Project;
use App\Models\PurchaseOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
  use RefreshDatabase;

  public function test_invoice_number_and_payment_amount_are_customizable_and_calculated(): void
  {
    $project = Project::create([
      'nama_project' => 'Invoice Test',
      'kustomer' => 'Customer A',
      'lokasi' => 'Jakarta',
      'nomor_quotation' => 'Q-INV-001',
    ]);
    $purchaseOrder = PurchaseOrder::create([
      'po_number' => 'PO-INV-001',
      'po_date' => '2026-10-12',
      'project_id' => $project->id,
    ]);

    $invoice = Invoice::create([
      'invoice_number' => 'CUSTOM-INV-001',
      'recipient' => 'Customer A',
      'purchase_order_id' => $purchaseOrder->id,
      'project_name' => 'Invoice Test',
      'invoice_date' => '2026-10-12',
      'payment_type' => 'full_payment',
      'payment_method' => 'mandiri',
      'dpp_enabled' => true,
      'ppn_enabled' => true,
    ]);

    InvoiceItem::create([
      'invoice_id' => $invoice->id,
      'description' => 'Service package',
      'quantity' => 2,
      'unit_price' => 100000,
    ]);

    $invoice->refresh();

    $this->assertSame('CUSTOM-INV-001', $invoice->invoice_number);
    $this->assertSame('INV26J001', $invoice->invoice_code);
    $this->assertSame(200000.0, (float) $invoice->subtotal);
    $this->assertSame(183333.0, (float) $invoice->dpp_amount);
    $this->assertSame(22000.0, (float) $invoice->ppn_amount);
    $this->assertSame(222000.0, (float) $invoice->grand_total);
    $this->assertSame(222000.0, (float) $invoice->payment_progress_amount);
  }
}