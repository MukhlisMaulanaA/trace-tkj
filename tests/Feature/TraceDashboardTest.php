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
}