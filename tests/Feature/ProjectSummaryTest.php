<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectExpenditure;
use App\Models\ProjectSummary;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectSummaryTest extends TestCase
{
  use RefreshDatabase;

  public function test_summary_calculates_live_values_from_purchase_orders_and_expenditures(): void
  {
    $project = Project::create([
      'nama_project' => 'Summary Test',
      'kustomer' => 'Customer A',
      'lokasi' => 'Jakarta',
      'nomor_quotation' => 'Q-001',
    ]);

    $firstPurchaseOrder = PurchaseOrder::create([
      'po_number' => 'PO-001',
      'po_date' => '2026-09-01',
      'project_id' => $project->id,
    ]);
    PurchaseOrderItem::create([
      'purchase_order_id' => $firstPurchaseOrder->id,
      'description' => 'Material package',
      'quantity' => 1,
      'sat' => 'lot',
      'unit_price' => 100000,
    ]);

    $secondPurchaseOrder = PurchaseOrder::create([
      'po_number' => 'PO-002',
      'po_date' => '2026-09-02',
      'project_id' => $project->id,
    ]);
    PurchaseOrderItem::create([
      'purchase_order_id' => $secondPurchaseOrder->id,
      'description' => 'Labour package',
      'quantity' => 1,
      'sat' => 'lot',
      'unit_price' => 50000,
    ]);

    $summary = $project->summary;
    $summary->update(['profit_percentage' => 10]);

    $summary->expenditures()->createMany([
      [
        'type' => ProjectExpenditure::TYPE_MATERIAL,
        'description' => 'Cable',
        'amount' => 20000,
        'expenditure_date' => '2026-09-03',
      ],
      [
        'type' => ProjectExpenditure::TYPE_LABOUR,
        'description' => 'Installation',
        'amount' => 10000,
        'expenditure_date' => '2026-09-04',
      ],
    ]);

    $summary->refresh();

    $this->assertSame(1, ProjectSummary::where('project_id', $project->id)->count());
    $this->assertSame('Customer A', $summary->owner_customer);
    $this->assertSame('Summary Test', $summary->project_name);
    $this->assertSame('PO-001, PO-002', $summary->po_numbers);
    $this->assertSame(150000.0, $summary->contract_value);
    $this->assertSame(3975.0, $summary->pph_amount);
    $this->assertSame(153975.0, $summary->final_contract_value);
    $this->assertSame(20000.0, $summary->material_expenditure);
    $this->assertSame(10000.0, $summary->labour_expenditure);
    $this->assertSame(30000.0, $summary->ml_expenditure);
    $this->assertSame(15398.0, $summary->final_profit);
    $this->assertSame(108577.0, $summary->remaining_budget);
    $this->assertSame(153975.0, $summary->balance);
  }

  public function test_nominal_profit_mode_overrides_percentage_calculation(): void
  {
    $project = Project::create([
      'nama_project' => 'Nominal Test',
      'kustomer' => 'Customer B',
      'lokasi' => 'Bandung',
      'nomor_quotation' => 'Q-002',
    ]);

    $purchaseOrder = $project->purchaseOrders()->create([
      'po_number' => 'PO-003',
      'po_date' => '2026-09-05',
    ]);
    PurchaseOrderItem::create([
      'purchase_order_id' => $purchaseOrder->id,
      'description' => 'Project package',
      'quantity' => 1,
      'sat' => 'lot',
      'unit_price' => 100000,
    ]);

    $summary = $project->summary;
    $summary->update([
      'profit_mode' => ProjectSummary::PROFIT_MODE_NOMINAL,
      'profit_percentage' => 50,
      'nominal_profit' => 12000,
    ]);

    $summary->refresh();

    $this->assertSame(12000.0, $summary->final_profit);
    $this->assertSame(90650.0, $summary->remaining_budget);
    $this->assertSame(102650.0, $summary->balance);
  }
}