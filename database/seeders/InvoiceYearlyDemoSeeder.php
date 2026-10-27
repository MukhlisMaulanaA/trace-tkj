<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvoiceYearlyDemoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $year = now()->year;
        $purchaseOrders = PurchaseOrder::query()->orderBy('id')->get();

        if ($purchaseOrders->isEmpty()) {
            $this->command?->warn('Invoice yearly demo data requires at least one purchase order.');

            return;
        }

        for ($month = 1; $month <= 12; $month++) {
            $date = Carbon::create($year, $month, 15, 10, 0, 0);
            $purchaseOrder = $purchaseOrders[($month - 1) % $purchaseOrders->count()];
            $amount = 10000000 + ($month * 2500000);
            $status = ['Sudah dibayar', 'Proses pembayaran', 'Draft', 'Belum dibayar'][($month - 1) % 4];
            $progress = match ($status) {
                'Sudah dibayar' => 100,
                'Proses pembayaran' => 50,
                'Draft' => 25,
                default => 0,
            };
            $invoiceNumber = sprintf('INV-DEMO-%d-%02d', $year, $month);

            $invoice = Invoice::updateOrCreate(
                ['invoice_number' => $invoiceNumber],
                [
                    'invoice_code' => $invoiceNumber,
                    'recipient' => $purchaseOrder->customer ?: 'Demo Customer',
                    'purchase_order_id' => $purchaseOrder->id,
                    'project_name' => $purchaseOrder->project?->nama_project ?: 'Demo Project',
                    'invoice_date' => $date->toDateString(),
                    'payment_type' => 'full_payment',
                    'payment_progress' => $progress,
                    'payment_method' => 'mandiri',
                    'status' => $status,
                    'discount_enabled' => false,
                    'dpp_enabled' => false,
                    'ppn_enabled' => false,
                ],
            );

            $invoice->items()->delete();
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'item_no' => 1,
                'description' => 'Yearly invoice summary demo item',
                'quantity' => 1,
                'unit_price' => $amount,
                'amount' => $amount,
            ]);
            $invoice->calculateTotals();

            DB::table('invoices')->where('id', $invoice->id)->update([
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
    }
}
