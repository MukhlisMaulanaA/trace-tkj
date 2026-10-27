<?php

namespace Tests\Feature;

use App\Filament\Pages\TraceDashboard;
use App\Models\PurchaseOrderProgress;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TraceDashboardTest extends TestCase
{
  use RefreshDatabase;

  public function test_attention_flags_purchase_orders_without_recent_activity(): void
  {
    Carbon::setTestNow('2026-09-28 12:00:00');
    $this->actingAs(User::factory()->create(['project_scope' => 'pusat']));

    $project = Project::create([
      'nama_project' => 'Dashboard Test',
      'kustomer' => 'Customer A',
      'lokasi' => 'Jakarta',
      'nomor_quotation' => 'Q-001',
    ]);

    $staleOrder = PurchaseOrder::create([
      'po_number' => 'PO-STALE',
      'po_date' => '2026-07-01',
      'project_id' => $project->id,
      'grand_total' => 100000,
    ]);
    PurchaseOrderProgress::create([
      'purchase_order_id' => $staleOrder->id,
      'title' => 'Invoice lama',
      'invoice_date' => '2026-07-15',
      'amount' => 10000,
    ]);

    $noActivityOrder = PurchaseOrder::create([
      'po_number' => 'PO-NO-ACTIVITY',
      'po_date' => '2026-07-01',
      'project_id' => $project->id,
    ]);

    $recentOrder = PurchaseOrder::create([
      'po_number' => 'PO-RECENT',
      'po_date' => '2026-07-01',
      'project_id' => $project->id,
      'grand_total' => 100000,
    ]);
    PurchaseOrderProgress::create([
      'purchase_order_id' => $recentOrder->id,
      'title' => 'Invoice baru',
      'invoice_date' => '2026-09-01',
      'amount' => 10000,
    ]);

    $completeOrder = PurchaseOrder::create([
      'po_number' => 'PO-COMPLETE',
      'po_date' => '2026-07-01',
      'project_id' => $project->id,
    ]);
    $completeOrder->forceFill(['grand_total' => 100000])->saveQuietly();
    PurchaseOrderProgress::create([
      'purchase_order_id' => $completeOrder->id,
      'title' => 'Invoice selesai',
      'invoice_date' => '2026-07-15',
      'amount' => 100000,
    ]);

    $attention = (new TraceDashboard)->dashboardData()['attention'];

    $this->assertTrue($attention->contains('title', 'PO-STALE'));
    $this->assertTrue($attention->contains('title', $noActivityOrder->po_number));
    $this->assertFalse($attention->contains('title', 'PO-RECENT'));
    $this->assertFalse($attention->contains('title', 'PO-COMPLETE'));
  }

  public function test_dashboard_metrics_use_the_selected_period(): void
  {
    Carbon::setTestNow('2026-09-28 12:00:00');
    $this->actingAs(User::factory()->create(['project_scope' => 'pusat']));

    $oldProject = Project::create([
      'nama_project' => 'Project Bulan Lalu',
      'kustomer' => 'Customer A',
      'lokasi' => 'Jakarta',
      'nomor_quotation' => 'Q-OLD',
    ]);
    $oldProject->forceFill(['created_at' => '2026-08-15', 'updated_at' => '2026-08-15'])->saveQuietly();
    $oldOrder = PurchaseOrder::create([
      'po_number' => 'PO-OLD',
      'po_date' => '2026-08-15',
      'project_id' => $oldProject->id,
    ]);
    $oldOrder->forceFill(['grand_total' => 100000])->saveQuietly();

    $currentProject = Project::create([
      'nama_project' => 'Project Bulan Ini',
      'kustomer' => 'Customer B',
      'lokasi' => 'Bandung',
      'nomor_quotation' => 'Q-CURRENT',
    ]);
    $currentOrder = PurchaseOrder::create([
      'po_number' => 'PO-CURRENT',
      'po_date' => '2026-09-15',
      'project_id' => $currentProject->id,
    ]);
    $currentOrder->forceFill(['grand_total' => 200000])->saveQuietly();

    $dashboard = new TraceDashboard;
    $dashboard->period = 'month';
    $data = $dashboard->dashboardData();

    $this->assertSame('1', $data['kpis'][0]['value']);
    $this->assertSame('1', $data['kpis'][1]['value']);
    $this->assertSame('Rp 200.000', $data['financial']['Total Nilai PO']);
    $this->assertSame('Project Bulan Ini', $data['recentProjects']->first()['title']);
    $this->assertSame('PO-CURRENT', $data['recentOrders']->first()['title']);
    $chartLabels = $data['chartRanges']['all']['points']->pluck('label')->all();
    $this->assertContains('Aug', $chartLabels);
    $this->assertContains('Sep', $chartLabels);

    $dashboard->period = 'all';
    $allTimeData = $dashboard->dashboardData();

    $this->assertSame('2', $allTimeData['kpis'][0]['value']);
    $this->assertSame('2', $allTimeData['kpis'][1]['value']);
    $this->assertSame('Rp 300.000', $allTimeData['financial']['Total Nilai PO']);
  }
}