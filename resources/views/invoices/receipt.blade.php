@php
  $companyName = 'PT. TANJUNG KARYA JAYA';
  $companyAddress = 'Perumahan Bumi Anugrah Sejahtera Blok B4 - No.3, Rt.009 / Rw.013, Kelurahan Kebalen, Kec. Babelan - Bekasi, Jawa Barat';
  $companyPhone = '0811-1020-770 - 0856-1539-431';
  $companyEmail = 'officetkj@tanjungkaryajaya.co.id / admin@tanjungkaryajaya.co.id';
  $date = fn($value) => $value ? \Carbon\Carbon::parse($value)->locale('id')->translatedFormat('d F Y') : '-';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Receipt - {{ $invoice->invoice_code }}</title>
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
    .receipt-title { margin: 24px 0 14px; text-align: center; font-size: 22pt; line-height: 1; font-weight: 800; letter-spacing: 1px; }
    .receipt-meta { width: 100%; border-collapse: collapse; margin-top: 15px; }
    .receipt-meta td { padding: 7px 0; border-bottom: 1px dotted #111827; vertical-align: top; font-size: 11pt; }
    .receipt-meta .label { width: 155px; font-weight: 700; }
    .receipt-meta .separator { width: 16px; text-align: center; }
    .receipt-meta .value { font-weight: 500; }
    .amount-box { margin-top: 32px; border-top: 2px solid #111827; border-bottom: 2px solid #111827; padding: 14px 0; }
    .amount-label { font-size: 14pt; font-weight: 800; }
    .amount-number { display: inline-block; min-width: 270px; margin-left: 18px; padding: 6px 20px; background: #e5e7eb; font-size: 16pt; font-weight: 800; }
    .amount-words { margin-top: 16px; font-size: 11pt; font-style: italic; text-transform: capitalize; }
    .signature { width: 42%; margin: 55px 0 0 auto; text-align: center; font-size: 10pt; }
    .signature-space { height: 65px; }
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
      a { color: inherit; text-decoration: none; }
    }
    @media screen and (max-width: 900px) {
      body { padding: 0; }
      .document { width: 100%; min-height: auto; padding: 24px; }
      .company-header { flex-direction: column; }
      .document-title, .document-subtitle { text-align: left; }
      .amount-number { min-width: 0; margin: 8px 0 0; }
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
        <h2 class="document-title">RECEIPT</h2>
        <p class="document-subtitle">PT. TANJUNG KARYA JAYA</p>
      </div>
    </header>
    <hr class="header-rule">
    <h1 class="receipt-title">KWITANSI</h1>
    <table class="receipt-meta">
      <tr><td class="label">No.</td><td class="separator">:</td><td class="value">{{ $invoice->invoice_code }}</td></tr>
      <tr><td class="label">Sudah Terima dari</td><td class="separator">:</td><td class="value">{{ $invoice->recipient }}</td></tr>
      <tr><td class="label">Untuk Pembayaran</td><td class="separator">:</td><td class="value">{{ $invoice->purchaseOrder->po_number }}</td></tr>
    </table>
    <div class="amount-box">
      <span class="amount-label">Jumlah Rp.</span>
      <span class="amount-number">{{ number_format((float) $invoice->grand_total, 2, ',', '.') }}</span>
      <div class="amount-words">{{ $amountWords }}</div>
    </div>
    <div class="signature">Bekasi, {{ $date(now()) }}<div class="signature-space"></div><strong>( ILHAM JAWAZ )</strong></div>
  </main>
</body>
</html>