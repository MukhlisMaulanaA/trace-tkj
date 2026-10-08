<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class InvoiceDocumentController extends Controller
{
  public function invoice(Invoice $invoice): View
  {
    $this->authorizeInvoice($invoice);
    $invoice->load(['purchaseOrder.project', 'items']);

    return view('invoices.document', [
      'invoice' => $invoice,
      'bank' => $this->bankDetails($invoice->payment_method),
    ]);
  }

  public function receipt(Invoice $invoice): View
  {
    $this->authorizeInvoice($invoice);
    $invoice->load('purchaseOrder');

    return view('invoices.receipt', [
      'invoice' => $invoice,
      'amountWords' => $this->terbilang((int) round((float) $invoice->grand_total)) . ' Rupiah',
    ]);
  }

  private function authorizeInvoice(Invoice $invoice): void
  {
    $invoice->loadMissing('purchaseOrder');
    Gate::authorize('view', $invoice->purchaseOrder);
  }

  private function bankDetails(string $method): array
  {
    return match ($method) {
      'bca_ilham' => ['bank' => 'Bank BCA (ILHAM JAWAZ)', 'account' => '521-139-9741', 'name' => 'ILHAM JAWAZ'],
      'bca_rohiman' => ['bank' => 'Bank BCA (ROHIMAN)', 'account' => '741-053-9483', 'name' => 'ROHIMAN'],
      default => ['bank' => 'Bank Mandiri', 'account' => '156-00-2075315-0', 'name' => 'PT. TANJUNG KARYA JAYA'],
    };
  }

  private function terbilang(int $number): string
  {
    $words = ['Nol', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];

    if ($number < 12) return $words[$number];
    if ($number < 20) return $this->terbilang($number - 10) . ' Belas';
    if ($number < 100) return $this->terbilang(intdiv($number, 10)) . ' Puluh' . ($number % 10 ? ' ' . $this->terbilang($number % 10) : '');
    if ($number < 200) return 'Seratus' . ($number - 100 ? ' ' . $this->terbilang($number - 100) : '');
    if ($number < 1000) return $this->terbilang(intdiv($number, 100)) . ' Ratus' . ($number % 100 ? ' ' . $this->terbilang($number % 100) : '');
    if ($number < 2000) return 'Seribu' . ($number - 1000 ? ' ' . $this->terbilang($number - 1000) : '');
    if ($number < 1000000) return $this->terbilang(intdiv($number, 1000)) . ' Ribu' . ($number % 1000 ? ' ' . $this->terbilang($number % 1000) : '');
    if ($number < 1000000000) return $this->terbilang(intdiv($number, 1000000)) . ' Juta' . ($number % 1000000 ? ' ' . $this->terbilang($number % 1000000) : '');

    return $this->terbilang(intdiv($number, 1000000000)) . ' Miliar' . ($number % 1000000000 ? ' ' . $this->terbilang($number % 1000000000) : '');
  }
}