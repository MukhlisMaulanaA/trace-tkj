<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectExpenditure;
use App\Models\ProjectProgress;
use App\Models\ProjectSummary;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrderProgress;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DashboardDemoSeeder extends Seeder
{
  use WithoutModelEvents;

  public function run(): void
  {
    $projects = [
      ['id' => 'P26D101', 'name' => 'Data Centre Cooling Upgrade', 'customer' => 'PT Arunika Energi', 'location' => 'Jakarta Selatan', 'source' => 'pusat', 'monthsAgo' => 11, 'profitMode' => 'percentage', 'profit' => 18, 'progress' => [[35, 290], [72, 18]], 'expenses' => [['material', 'Cooling units and ducting', 185000000, 300], ['labour', 'Mechanical installation', 72000000, 250]]],
      ['id' => 'P26D102', 'name' => 'Retail Branch Network Rollout', 'customer' => 'PT Satu Ritel Indonesia', 'location' => 'Bandung', 'source' => 'pusat', 'monthsAgo' => 9, 'profitMode' => 'nominal', 'profit' => 78000000, 'progress' => [[30, 220], [64, 90]], 'expenses' => [['material', 'Router and firewall packages', 92000000, 180], ['labour', 'Branch deployment crew', 44000000, 75]]],
      ['id' => 'P26D103', 'name' => 'Water Treatment Telemetry', 'customer' => 'PDAM Tirta Jaya', 'location' => 'Surabaya', 'source' => 'pusat', 'monthsAgo' => 7, 'profitMode' => 'percentage', 'profit' => 12, 'progress' => [[25, 190], [58, 35]], 'expenses' => [['material', 'Telemetry sensors', 68000000, 150], ['labour', 'SCADA integration', 31000000, 32]]],
      ['id' => 'P26D104', 'name' => 'District 8 Warehouse Automation', 'customer' => 'PT Logistik Cepat', 'location' => 'Bekasi', 'source' => 'distrik_8', 'monthsAgo' => 6, 'profitMode' => 'percentage', 'profit' => 16, 'progress' => [[42, 120], [78, 12]], 'expenses' => [['material', 'Barcode and scanner equipment', 120000000, 105], ['labour', 'Automation commissioning', 56000000, 20]]],
      ['id' => 'P26D105', 'name' => 'Hospital Access Control', 'customer' => 'RS Harapan Sehat', 'location' => 'Depok', 'source' => 'distrik_8', 'monthsAgo' => 4, 'profitMode' => 'nominal', 'profit' => 46000000, 'progress' => [[45, 75]], 'expenses' => [['material', 'Access control panels', 74000000, 70], ['labour', 'Door controller installation', 29000000, 65]]],
      ['id' => 'P26D106', 'name' => 'Campus Fibre Backbone', 'customer' => 'Universitas Cakrawala', 'location' => 'Yogyakarta', 'source' => 'pusat', 'monthsAgo' => 3, 'profitMode' => 'percentage', 'profit' => 14, 'progress' => [[38, 42]], 'expenses' => [['material', 'Fibre optic backbone', 98000000, 40], ['labour', 'Fibre splicing team', 36000000, 38]]],
      ['id' => 'P26D107', 'name' => 'Cold Storage Monitoring', 'customer' => 'PT Segar Makmur', 'location' => 'Tangerang', 'source' => 'distrik_8', 'monthsAgo' => 2, 'profitMode' => 'percentage', 'profit' => 10, 'progress' => [], 'expenses' => [['material', 'Temperature monitoring kits', 42000000, 65]]],
      ['id' => 'P26D108', 'name' => 'Manufacturing Safety System', 'customer' => 'PT Baja Prima', 'location' => 'Karawang', 'source' => 'pusat', 'monthsAgo' => 1, 'profitMode' => 'nominal', 'profit' => 32000000, 'progress' => [[22, 12]], 'expenses' => [['material', 'Safety sensors and beacons', 52000000, 15], ['labour', 'Safety system configuration', 18000000, 10]]],
      ['id' => 'P26D109', 'name' => 'Hotel Wi-Fi Refresh', 'customer' => 'Nusantara Hospitality', 'location' => 'Bali', 'source' => 'pusat', 'monthsAgo' => 0, 'profitMode' => 'percentage', 'profit' => 11, 'progress' => [[18, 5]], 'expenses' => [['material', 'Wi-Fi access points', 36000000, 4]]],
    ];

    foreach ($projects as $definition) {
      $this->seedProject($definition);
    }
  }

  private function seedProject(array $definition): void
  {
    $projectDate = now()->subMonths($definition['monthsAgo'])->startOfMonth()->addDays(3);
    $project = Project::updateOrCreate(
      ['id' => $definition['id']],
      [
        'nama_project' => $definition['name'],
        'kustomer' => $definition['customer'],
        'kontak_person' => 'Demo Contact',
        'lokasi' => $definition['location'],
        'nomor_quotation' => 'QT-DEMO-' . substr($definition['id'], -3),
        'pic' => 'Demo Project Manager',
        'project_source' => $definition['source'],
      ],
    );

    DB::table('projects')->where('id', $project->id)->update([
      'created_at' => $projectDate,
      'updated_at' => $projectDate,
    ]);

    $summary = ProjectSummary::updateOrCreate(
      ['project_id' => $project->id],
      [
        'profit_mode' => $definition['profitMode'],
        'profit_percentage' => $definition['profitMode'] === ProjectSummary::PROFIT_MODE_PERCENTAGE ? $definition['profit'] : 0,
        'nominal_profit' => $definition['profitMode'] === ProjectSummary::PROFIT_MODE_NOMINAL ? $definition['profit'] : 0,
      ],
    );

    ProjectProgress::where('project_id', $project->id)->delete();
    foreach ($definition['progress'] as [$percentage, $daysAgo]) {
      ProjectProgress::create([
        'project_id' => $project->id,
        'waktu_progres' => now()->subDays($daysAgo),
        'persentase' => $percentage,
        'keterangan' => 'Seeded dashboard progress update',
        'is_system' => false,
      ]);
    }

    $summary->expenditures()->delete();
    foreach ($definition['expenses'] as [$type, $description, $amount, $daysAgo]) {
      $summary->expenditures()->create([
        'type' => $type,
        'description' => $description,
        'amount' => $amount,
        'expenditure_date' => now()->subDays($daysAgo),
      ]);
    }

    foreach ($this->purchaseOrdersFor($definition) as $purchaseOrder) {
      $this->seedPurchaseOrder($project, $purchaseOrder);
    }
  }

  private function purchaseOrdersFor(array $definition): array
  {
    $suffix = substr($definition['id'], -3);
    $baseAmount = 240000000 + ((int) $suffix * 1500000);

    return [
      [
        'number' => 'PO-DEMO-' . $suffix . '-A',
        'date' => now()->subMonths($definition['monthsAgo'])->addDays(12),
        'amount' => $baseAmount,
        'status' => 'submitted',
        'progress' => $definition['progress'] === [] ? [] : [[$baseAmount * 0.28, max(8, $definition['monthsAgo'] * 28)]],
      ],
      [
        'number' => 'PO-DEMO-' . $suffix . '-B',
        'date' => now()->subMonths($definition['monthsAgo'])->addDays(20),
        'amount' => (int) ($baseAmount * 0.55),
        'status' => $definition['monthsAgo'] % 2 === 0 ? 'draft' : 'submitted',
        'progress' => $definition['monthsAgo'] >= 6 ? [] : [[$baseAmount * 0.2, max(5, $definition['monthsAgo'] * 18)]],
      ],
    ];
  }

  private function seedPurchaseOrder(Project $project, array $definition): void
  {
    $purchaseOrder = PurchaseOrder::updateOrCreate(
      ['po_number' => $definition['number']],
      [
        'po_code' => str_replace('PO-DEMO-', 'POD', $definition['number']),
        'po_date' => $definition['date'],
        'type' => 'project',
        'project_id' => $project->id,
        'customer' => $project->kustomer,
        'location' => $project->lokasi,
        'quotation_no' => $project->nomor_quotation,
        'pic' => $project->pic,
        'status' => $definition['status'],
        'notes' => 'Seeded dashboard demo purchase order',
      ],
    );

    $purchaseOrder->items()->delete();
    PurchaseOrderItem::create([
      'purchase_order_id' => $purchaseOrder->id,
      'item_no' => 1,
      'description' => 'Demo project equipment and installation package',
      'quantity' => 1,
      'sat' => 'lot',
      'unit_price' => $definition['amount'],
      'total_price' => $definition['amount'],
    ]);
    $purchaseOrder->calculateTotals();

    $purchaseOrder->progresses()->delete();
    foreach ($definition['progress'] as [$amount, $daysAgo]) {
      PurchaseOrderProgress::create([
        'purchase_order_id' => $purchaseOrder->id,
        'title' => 'Demo invoice payment',
        'description' => 'Seeded dashboard demo payment activity',
        'invoice_date' => now()->subDays($daysAgo),
        'amount' => $amount,
        'is_system' => false,
      ]);
    }
  }
}