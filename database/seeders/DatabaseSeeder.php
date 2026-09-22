<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectExpenditure;
use App\Models\ProjectProgress;
use App\Models\ProjectSummary;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrderProgress;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Demo Administrator',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );

        $this->seedProjectAlpha();
        $this->seedProjectBeta();
    }

    private function seedProjectAlpha(): void
    {
        $project = Project::updateOrCreate(
            ['id' => 'P26I001'],
            [
                'nama_project' => 'Office Network Installation',
                'kustomer' => 'PT Nusantara Teknologi',
                'kontak_person' => 'Budi Santoso',
                'lokasi' => 'Jakarta Selatan',
                'nomor_quotation' => 'QT-2026-001',
                'pic' => 'Andi Pratama',
            ],
        );

        ProjectSummary::updateOrCreate(
            ['project_id' => $project->id],
            [
                'profit_mode' => ProjectSummary::PROFIT_MODE_PERCENTAGE,
                'profit_percentage' => 15,
                'nominal_profit' => 0,
            ],
        );

        $this->seedProjectProgress($project, [
            [25, 'Site survey completed'],
            [60, 'Cabling and equipment installation in progress'],
        ]);

        $firstPurchaseOrder = $this->seedPurchaseOrder(
            project: $project,
            poCode: 'PO26I001',
            poNumber: 'PO-NT-001',
            date: '2026-09-02',
            items: [
                ['description' => 'Network switches and rack equipment', 'quantity' => 1, 'sat' => 'lot', 'unit_price' => 85000000],
                ['description' => 'Structured cabling materials', 'quantity' => 1, 'sat' => 'lot', 'unit_price' => 35000000],
            ],
        );

        $secondPurchaseOrder = $this->seedPurchaseOrder(
            project: $project,
            poCode: 'PO26I002',
            poNumber: 'PO-NT-002',
            date: '2026-09-10',
            items: [
                ['description' => 'Additional access points', 'quantity' => 4, 'sat' => 'unit', 'unit_price' => 4500000],
            ],
        );

        $this->seedPurchaseOrderProgress($firstPurchaseOrder, [
            ['title' => 'Down payment', 'amount' => 60000000, 'date' => '2026-09-05'],
            ['title' => 'Installation invoice', 'amount' => 30000000, 'date' => '2026-09-16'],
        ]);
        $this->seedPurchaseOrderProgress($secondPurchaseOrder, [
            ['title' => 'Additional equipment invoice', 'amount' => 18000000, 'date' => '2026-09-18'],
        ]);

        $this->seedExpenditures($project, [
            [ProjectExpenditure::TYPE_MATERIAL, 'Fibre optic cable and connectors', 28000000, '2026-09-06'],
            [ProjectExpenditure::TYPE_MATERIAL, 'Cable trays and accessories', 12000000, '2026-09-09'],
            [ProjectExpenditure::TYPE_LABOUR, 'Installation crew - phase one', 22000000, '2026-09-12'],
            [ProjectExpenditure::TYPE_LABOUR, 'Configuration and testing', 15000000, '2026-09-19'],
        ]);
    }

    private function seedProjectBeta(): void
    {
        $project = Project::updateOrCreate(
            ['id' => 'P26I002'],
            [
                'nama_project' => 'Warehouse CCTV Upgrade',
                'kustomer' => 'CV Maju Bersama',
                'kontak_person' => 'Siti Rahma',
                'lokasi' => 'Bekasi',
                'nomor_quotation' => 'QT-2026-002',
                'pic' => 'Rina Wulandari',
            ],
        );

        ProjectSummary::updateOrCreate(
            ['project_id' => $project->id],
            [
                'profit_mode' => ProjectSummary::PROFIT_MODE_NOMINAL,
                'profit_percentage' => 10,
                'nominal_profit' => 25000000,
            ],
        );

        $this->seedProjectProgress($project, [
            [40, 'Camera mounting completed'],
            [85, 'System configuration and handover preparation'],
        ]);

        $purchaseOrder = $this->seedPurchaseOrder(
            project: $project,
            poCode: 'PO26I003',
            poNumber: 'PO-CCTV-001',
            date: '2026-09-04',
            items: [
                ['description' => 'CCTV cameras', 'quantity' => 16, 'sat' => 'unit', 'unit_price' => 5500000],
                ['description' => 'NVR and storage system', 'quantity' => 1, 'sat' => 'lot', 'unit_price' => 28000000],
            ],
        );

        $this->seedPurchaseOrderProgress($purchaseOrder, [
            ['title' => 'CCTV equipment down payment', 'amount' => 50000000, 'date' => '2026-09-08'],
            ['title' => 'Final installation invoice', 'amount' => 40000000, 'date' => '2026-09-20'],
        ]);

        $this->seedExpenditures($project, [
            [ProjectExpenditure::TYPE_MATERIAL, 'Mounting brackets and conduit', 18000000, '2026-09-11'],
            [ProjectExpenditure::TYPE_LABOUR, 'CCTV installation team', 26000000, '2026-09-15'],
        ]);
    }

    private function seedPurchaseOrder(
        Project $project,
        string $poCode,
        string $poNumber,
        string $date,
        array $items,
    ): PurchaseOrder {
        $purchaseOrder = PurchaseOrder::updateOrCreate(
            ['po_number' => $poNumber],
            [
                'po_code' => $poCode,
                'po_date' => $date,
                'project_id' => $project->id,
                'customer' => $project->kustomer,
                'location' => $project->lokasi,
                'quotation_no' => $project->nomor_quotation,
                'pic' => $project->pic,
                'status' => 'submitted',
                'notes' => 'Seeded demo purchase order',
            ],
        );

        $purchaseOrder->items()->delete();

        foreach ($items as $item) {
            $totalPrice = $item['quantity'] * $item['unit_price'];

            PurchaseOrderItem::create([
                'purchase_order_id' => $purchaseOrder->id,
                'item_no' => $purchaseOrder->items()->max('item_no') + 1,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'sat' => $item['sat'],
                'unit_price' => $item['unit_price'],
                'total_price' => $totalPrice,
            ]);
        }

        $purchaseOrder->calculateTotals();

        return $purchaseOrder->refresh();
    }

    private function seedProjectProgress(Project $project, array $progresses): void
    {
        ProjectProgress::query()
            ->where('project_id', $project->id)
            ->where('is_system', false)
            ->delete();

        foreach ($progresses as [$percentage, $description]) {
            ProjectProgress::create([
                'project_id' => $project->id,
                'waktu_progres' => now()->subDays(10 - $percentage / 10),
                'persentase' => $percentage,
                'keterangan' => $description,
                'is_system' => false,
            ]);
        }
    }

    private function seedPurchaseOrderProgress(PurchaseOrder $purchaseOrder, array $progresses): void
    {
        $purchaseOrder->progresses()->delete();

        foreach ($progresses as $progress) {
            $invoice = PurchaseOrderProgress::create([
                'purchase_order_id' => $purchaseOrder->id,
                'title' => $progress['title'],
                'description' => 'Seeded demo invoice progress',
                'invoice_date' => $progress['date'],
                'amount' => $progress['amount'],
                'is_system' => false,
            ]);

            $invoice->calculatePercentage();
            $invoice->saveQuietly();
        }
    }

    private function seedExpenditures(Project $project, array $expenditures): void
    {
        $summary = $project->summary()->first();

        if (!$summary) {
            $summary = $project->summary()->create([
                'profit_mode' => ProjectSummary::PROFIT_MODE_PERCENTAGE,
                'profit_percentage' => 10,
                'nominal_profit' => 0,
            ]);
        }

        $summary->expenditures()->delete();

        foreach ($expenditures as [$type, $description, $amount, $date]) {
            $summary->expenditures()->create([
                'type' => $type,
                'description' => $description,
                'amount' => $amount,
                'expenditure_date' => $date,
            ]);
        }
    }
}
