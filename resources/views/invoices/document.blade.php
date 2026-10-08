@php
  $companyName = 'PT. TANJUNG KARYA JAYA';
  $companyAddress = 'Perumahan Bumi Anugrah Sejahtera Blok B4 - No.3, Rt.009 / Rw.013, Kelurahan Kebalen, Kec. Babelan - Bekasi, Jawa Barat';
  $companyPhone = '0811-1020-770 - 0856-1539-431';
  $companyEmail = 'officetkj@tanjungkaryajaya.co.id / admin@tanjungkaryajaya.co.id';
  $money = fn($value) => number_format((float) $value, 0, ',', '.');
  $date = fn($value) => $value ? \Carbon\Carbon::parse($value)->locale('id')->translatedFormat('d F Y') : '-';
  $subtotalAfterDiscount = max(0, (float) $invoice->subtotal - (float) $invoice->discount_amount);
  $ppnRate = $invoice->dpp_enabled ? 12 : 11;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Invoice - {{ $invoice->invoice_code }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    @page { size: A4 portrait; margin: 12mm 12mm 0mm 12mm; }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; background: #e5e7eb; color: #111827; font-family: Arial, Helvetica, sans-serif; font-size: 10pt; line-height: 1.35; }
    body { padding: 24px 0; }
    .document { width: 210mm; min-height: 297mm; margin: 0 auto; padding: 12mm; background: #fff; box-shadow: 0 3px 20px rgba(0, 0, 0, .12); }
    .company-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; margin-bottom: 10px; }
    .company-brand { display: flex; align-items: flex-start; gap: 12px; min-width: 0; }
    .company-logo { width: 70px; height: 70px; object-fit: contain; flex: 0 0 70px; }
    .company-info { min-width: 0; }
    .company-name { margin: 0 0 3px; font-size: 16pt; line-height: 1.1; font-weight: 800; letter-spacing: .1px; }
    .company-address, .company-contact { margin: 0; font-size: 7.5pt; line-height: 1.35; color: #374151; }
    .document-title { margin: 0; text-align: right; font-size: 20pt; line-height: 1; font-weight: 800; letter-spacing: .8px; }
    .document-subtitle { margin-top: 5px; text-align: right; font-size: 7.5pt; color: #6b7280; }
    .header-rule { border: 0; border-top: 2px solid #111827; margin: 8px 0 12px; }
    .meta { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    .meta td { padding: 2px 0; vertical-align: top; font-size: 9pt; }
    .meta .label { width: 105px; font-weight: 700; }
    .meta .separator { width: 12px; text-align: center; }
    .meta .value { font-weight: 500; }
    .meta .right-label { width: 95px; padding-left: 20px; font-weight: 700; }
    .items-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .items-table th, .items-table td { border: 1px solid #111827; }
    .items-table thead { display: table-header-group; }
    .items-table th { padding: 6px 5px; text-align: center; font-size: 8.5pt; font-weight: 800; background: #f3f4f6; vertical-align: middle; }
    .items-table td { padding: 5px; vertical-align: top; font-size: 8.5pt; }
    .col-no { width: 8%; text-align: center; }
    .col-description { width: 48%; }
    .col-qty { width: 10%; text-align: center; }
    .col-unit { width: 17%; text-align: right; }
    .col-total { width: 17%; text-align: right; }
    .item-row td { height: 175px; }
    .item-description { white-space: pre-line; line-height: 1.4; }
    .amount { white-space: nowrap; text-align: right; }
    .summary-row td { height: 30px; vertical-align: middle; }
    .summary-label { padding-left: 38px !important; font-weight: 700; }
    .summary-value { text-align: right; white-space: nowrap; font-weight: 700; }
    .grand-total td { border-top: 2px solid #111827; font-size: 9pt; font-weight: 800; }
    .payment-progress { font-weight: 700; }
    .footer-grid { width: 100%; border-collapse: collapse; margin-top: 20px; }
    .footer-grid td { width: 50%; vertical-align: top; font-size: 8.5pt; }
    .signature { text-align: center; padding-left: 80px; }
    .signature-space { height: 48px; }
    .print-toolbar { position: fixed; top: 18px; right: 18px; z-index: 1000; display: flex; gap: 8px; }
    .print-button, .back-button { border-radius: 6px; font-size: 13px; font-weight: 700; padding: 9px 14px; }
    .print-button { border: 0; background: #2563eb; color: #fff; cursor: pointer; }
    .print-button:hover { background: #1d4ed8; }
    .back-button { background: #fff; border: 1px solid #d1d5db; color: #111827; text-decoration: none; }
    @media print {
      html, body { background: #fff; }
      body { padding: 0; }
      .print-toolbar { display: none !important; }
      .document { width: auto; min-height: auto; margin: 0; padding: 0; box-shadow: none; }
      .items-table tr, .footer-grid { break-inside: avoid; page-break-inside: avoid; }
      a { color: inherit; text-decoration: none; }
    }
    @media screen and (max-width: 900px) {
      body { padding: 0; }
      .document { width: 100%; min-height: auto; padding: 24px; }
      .company-header { flex-direction: column; }
      .document-title, .document-subtitle { text-align: left; }
    }
  </style>
</head>
<body>
  <div class="print-toolbar">
    <a href="{{ url()->previous() }}" class="back-button">&#8592; Kembali</a>
    <button type="button" class="print-button" onclick="window.print()">Save as PDF</button>
  </div>
  <main class="document">
    <header class="company-header">
      <div class="company-brand">
        <img src="{{ asset('images/logo-tkj.png') }}" alt="Logo {{ $companyName }}" class="company-logo">
        <div class="company-info">
          <h1 class="company-name">{{ $companyName }}</h1>
          <p class="company-address">{{ $companyAddress }}</p>
          <p class="company-contact">Telp : {{ $companyPhone }}</p>
          <p class="company-contact">Email : {{ $companyEmail }}</p>
          <p class="company-contact">Website : tanjungkaryajaya.co.id</p>
        </div>
      </div>
      <div>
        <h2 class="document-title">INVOICE</h2>
        <p class="document-subtitle">PT. TANJUNG KARYA JAYA</p>
      </div>
    </header>
    <hr class="header-rule">
    <table class="meta">
      <tr><td class="label">To</td><td class="separator">:</td><td class="value">{{ $invoice->recipient }}</td><td class="right-label">Invoice ID</td><td class="separator">:</td><td class="value">{{ $invoice->invoice_code }}</td></tr>
      <tr><td class="label">Purchase Order</td><td class="separator">:</td><td class="value">{{ $invoice->purchaseOrder->po_number }}</td><td class="right-label">Date</td><td class="separator">:</td><td class="value">{{ $date($invoice->invoice_date) }}</td></tr>
      <tr><td class="label">Project</td><td class="separator">:</td><td class="value">{{ $invoice->project_name ?: '-' }}</td><td class="right-label">Payment</td><td class="separator">:</td><td class="value">{{ ucwords(str_replace('_', ' ', $invoice->payment_type)) }}</td></tr>
    </table>
    <table class="items-table">
      <thead><tr><th class="col-no">No</th><th class="col-description">Description</th><th class="col-qty">QTY</th><th class="col-unit">Unit Price (Rp)</th><th class="col-total">Amount (Rp)</th></tr></thead>
      <tbody>
        @forelse ($invoice->items as $item)
          <tr class="item-row"><td class="col-no">{{ $item->item_no }}</td><td class="col-description"><div class="item-description">{{ $item->description }}</div></td><td class="col-qty">{{ rtrim(rtrim(number_format((float) $item->quantity, 2, ',', '.'), '0'), ',') }}</td><td class="amount">{{ $money($item->unit_price) }}</td><td class="amount">{{ $money($item->amount) }}</td></tr>
        @empty
          <tr class="item-row"><td colspan="5" style="text-align:center;">Belum ada item invoice.</td></tr>
        @endforelse
        <tr class="summary-row"><td></td><td class="summary-label">SUB TOTAL</td><td></td><td></td><td class="summary-value">{{ $money($invoice->subtotal) }}</td></tr>
        @if ($invoice->discount_enabled)<tr class="summary-row"><td></td><td class="summary-label">DISCOUNT</td><td></td><td></td><td class="summary-value">- {{ $money($invoice->discount_amount) }}</td></tr>@endif
        <tr class="summary-row"><td></td><td class="summary-label">TOTAL</td><td></td><td></td><td class="summary-value">{{ $money($subtotalAfterDiscount) }}</td></tr>
        <tr class="summary-row"><td></td><td class="summary-label">PAYMENT <span class="payment-progress">{{ $invoice->payment_progress ?: ($invoice->payment_type === 'full_payment' ? '100%' : '-') }}</span></td><td></td><td></td><td class="summary-value">{{ $money($invoice->payment_progress_amount ?: $invoice->grand_total) }}</td></tr>
        @if ($invoice->dpp_enabled)<tr class="summary-row"><td></td><td class="summary-label">DPP LAINNYA</td><td></td><td></td><td class="summary-value">{{ $money($invoice->dpp_amount) }}</td></tr>@endif
        @if ($invoice->ppn_enabled)<tr class="summary-row"><td></td><td class="summary-label">PPN <span class="payment-progress">{{ $ppnRate }}%</span></td><td></td><td></td><td class="summary-value">{{ $money($invoice->ppn_amount) }}</td></tr>@endif
        <tr class="summary-row grand-total"><td></td><td class="summary-label">GRAND TOTAL (IDR)</td><td></td><td></td><td class="summary-value">{{ $money($invoice->grand_total) }}</td></tr>
      </tbody>
    </table>
    <table class="footer-grid">
      <tr>
        <td><strong>PAYMENT TO THE FOLLOWING ACCOUNT :</strong><br>BANK <span style="margin-left: 44px;">: {{ $bank['bank'] }}</span><br>ACCOUNT NO <span style="margin-left: 16px;">: {{ $bank['account'] }}</span><br>ACCOUNT NAME <span style="margin-left: 4px;">: {{ $bank['name'] }}</span></td>
        <td class="signature">Bekasi, {{ $date(now()) }}<div class="signature-space"></div><strong>( ILHAM JAWAZ )</strong></td>
      </tr>
    </table>
  </main>
</body>
</html>