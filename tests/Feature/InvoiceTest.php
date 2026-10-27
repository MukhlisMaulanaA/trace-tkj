<?php

namespace Tests\Feature;

use App\Exports\InvoiceYearlySummaryExport;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
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
            'project_name' => 'Invoice Test',
            'invoice_date' => '2026-10-12',
            'payment_type' => 'Progress Billing',
            'payment_progress' => 30,
            'payment_method' => 'mandiri',
            'discount_enabled' => true,
            'discount_type' => 'percent',
            'discount_percent' => 10,
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
        $user = User::factory()->create(['project_scope' => 'pusat']);

        $this->assertSame('CUSTOM-INV-001', $invoice->invoice_number);
        $this->assertSame('INV26J001', $invoice->invoice_code);
        $this->assertSame('Draft', $invoice->status);
        $this->assertSame(200000.0, (float) $invoice->subtotal);
        $this->assertNull($invoice->purchase_order_id);
        $this->assertSame('Progress Billing', $invoice->payment_type);
        $this->assertSame(49500.0, (float) $invoice->dpp_amount);
        $this->assertSame(5940.0, (float) $invoice->ppn_amount);
        $this->assertSame(59940.0, (float) $invoice->grand_total);
        $this->assertSame(54000.0, (float) $invoice->payment_progress_amount);
        $this->assertTrue(Invoice::accessibleBy($user)->whereKey($invoice)->exists());

        $this->actingAs($user)
            ->get(route('invoices.document', $invoice))
            ->assertOk();
        $this->actingAs($user)
            ->get(route('invoices.receipt', $invoice))
            ->assertOk();
        $this->actingAs($user)
            ->get(route('invoices.excel', $invoice))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $summary = new InvoiceYearlySummaryExport(
            Invoice::accessibleBy($user)->with('purchaseOrder')->whereYear('created_at', now()->year)->get(),
            now()->year,
        );
        $summaryFile = $summary->download();
        $sheet = IOFactory::load($summaryFile)->getActiveSheet();

        $this->assertSame('Monitoring Invoices '.now()->year, $sheet->getCell('A1')->getValue());
        $this->assertSame('Bulan', $sheet->getCell('A2')->getValue());
        $this->assertSame('CUSTOM-INV-001', $sheet->getCell('D3')->getValue());
        $this->assertSame('Customer A', $sheet->getCell('G3')->getValue());
        $this->assertSame(59940.0, (float) $sheet->getCell('H3')->getValue());
        unlink($summaryFile);

        $this->actingAs($user)
            ->get(route('invoices.yearly-summary'))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $previousYear = now()->year - 1;
        DB::table('invoices')->where('id', $invoice->id)->update([
            'created_at' => now()->setYear($previousYear),
        ]);

        $previousYearResponse = $this->actingAs($user)
            ->get(route('invoices.yearly-summary', ['year' => $previousYear]));

        $previousYearResponse->assertOk();
        $this->assertStringContainsString(
            'monitoring-invoices-'.$previousYear.'.xlsx',
            (string) $previousYearResponse->headers->get('content-disposition'),
        );

        $this->actingAs($user)
            ->get(route('invoices.yearly-summary', ['year' => $previousYear - 1]))
            ->assertNotFound();
    }
}
