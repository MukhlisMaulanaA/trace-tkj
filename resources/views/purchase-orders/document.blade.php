@php
  use Illuminate\Support\Str;

  $po = $record;

  /*
    |--------------------------------------------------------------------------
    | DATA DASAR
    |--------------------------------------------------------------------------
    */
  $companyName = 'PT. TANJUNG KARYA JAYA';

  $companyAddress =
      'Perumahan Bumi Anugrah Sejahtera Blok B4 - No.3, ' .
      'Rt.009 / Rw.013, Kelurahan Kebalen, ' .
      'Kec. Babelan - Bekasi, Jawa Barat';

  $companyPhone = '0811-1020-770 - 0856-1539-431';

  $companyEmail = 'officetkj@tanjungkaryajaya.co.id / ' . 'admin@tanjungkaryajaya.co.id';

  /*
    |--------------------------------------------------------------------------
    | ITEMS
    |--------------------------------------------------------------------------
    */

  $items = $po->items->sortBy('item_no')->values();

  /*
    |--------------------------------------------------------------------------
    | TAX / DISCOUNT
    |
    | Nilai amount diambil dari database agar historical PO konsisten.
    |--------------------------------------------------------------------------
    */

  $subtotal = (float) ($po->subtotal ?? 0);

  $ppnEnabled = (bool) ($po->ppn_enabled ?? false);

  // Yang ditampilkan kepada user/client tetap 12%.
  $ppnDisplayPercentage = (float) ($po->ppn_percentage ?? 12);

  // Amount yang sudah disimpan secara historis.
  $ppnAmount = $ppnEnabled ? (float) ($po->ppn_amount ?? 0) : 0;

  $subtotalAfterPpn = $subtotal + $ppnAmount;

  $discountEnabled = (bool) ($po->discount_enabled ?? false);

  $discountPercentage = $discountEnabled ? (float) ($po->discount_percent ?? 0) : 0;

  $discountAmount = $discountEnabled ? (float) ($po->discount_amount ?? 0) : 0;

  $grandTotal = (float) ($po->grand_total ?? $subtotalAfterPpn - $discountAmount);

  /*
    |--------------------------------------------------------------------------
    | FORMATTER
    |--------------------------------------------------------------------------
    */

  $money = function ($value): string {
      return 'Rp ' . number_format((float) $value, 0, ',', '.');
  };

  $qty = function ($value): string {
      $number = (float) $value;

      if (floor($number) == $number) {
          return number_format($number, 0, ',', '.');
      }

      return number_format($number, 2, ',', '.');
  };

  /*
    |--------------------------------------------------------------------------
    | DATE
    |--------------------------------------------------------------------------
    */

  $poDate = $po->po_date ? \Carbon\Carbon::parse($po->po_date)->locale('id')->translatedFormat('d F Y') : '-';
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">

  <title>
    Purchase Order - {{ $po->po_number }}
  </title>

  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <style>
    /*
        |--------------------------------------------------------------------------
        | PAGE
        |--------------------------------------------------------------------------
        */

    @page {
      size: A4 portrait;
      margin: 12mm 12mm 0mm 12mm;
    }

    /*
        |--------------------------------------------------------------------------
        | RESET
        |--------------------------------------------------------------------------
        */

    * {
      box-sizing: border-box;
    }

    html,
    body {
      margin: 0;
      padding: 0;
      background: #e5e7eb;
      color: #111827;
      font-family: Arial, Helvetica, sans-serif;
      font-size: 10pt;
      line-height: 1.35;
    }

    body {
      padding: 24px 0;
    }

    /*
        |--------------------------------------------------------------------------
        | DOCUMENT
        |--------------------------------------------------------------------------
        */

    .po-document {
      width: 210mm;
      min-height: 297mm;
      margin: 0 auto;
      padding: 12mm;
      background: #ffffff;
      box-shadow: 0 3px 20px rgba(0, 0, 0, 0.12);
    }

    /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

    .company-header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 20px;
      margin-bottom: 10px;
    }

    .company-brand {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      min-width: 0;
    }

    .company-logo {
      width: 70px;
      height: 70px;
      object-fit: contain;
      flex: 0 0 70px;
    }

    .company-info {
      min-width: 0;
    }

    .company-name {
      margin: 0 0 3px;
      font-size: 16pt;
      line-height: 1.1;
      font-weight: 800;
      letter-spacing: 0.1px;
    }

    .company-address,
    .company-contact {
      margin: 0;
      font-size: 7.5pt;
      line-height: 1.35;
      color: #374151;
    }

    .document-title {
      margin: 0;
      text-align: right;
      font-size: 20pt;
      line-height: 1;
      font-weight: 800;
      letter-spacing: 0.8px;
    }

    .document-subtitle {
      margin-top: 5px;
      text-align: right;
      font-size: 7.5pt;
      color: #6b7280;
    }

    /*
        |--------------------------------------------------------------------------
        | HEADER LINE
        |--------------------------------------------------------------------------
        */

    .header-rule {
      border: 0;
      border-top: 2px solid #111827;
      margin: 8px 0 12px;
    }

    /*
        |--------------------------------------------------------------------------
        | PO META
        |--------------------------------------------------------------------------
        */

    .po-meta {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 14px;
    }

    .po-meta td {
      padding: 2px 0;
      vertical-align: top;
      font-size: 9pt;
    }

    .po-meta .label {
      width: 70px;
      font-weight: 700;
    }

    .po-meta .separator {
      width: 12px;
      text-align: center;
    }

    .po-meta .value {
      font-weight: 500;
    }

    .po-meta .right-label {
      width: 110px;
      padding-left: 20px;
      font-weight: 700;
    }

    /*
        |--------------------------------------------------------------------------
        | ITEM TABLE
        |--------------------------------------------------------------------------
        */

    .items-table {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
    }

    .items-table th,
    .items-table td {
      border: 1px solid #111827;
    }

    .items-table thead {
      display: table-header-group;
    }

    .items-table th {
      padding: 6px 5px;
      text-align: center;
      font-size: 8.5pt;
      font-weight: 800;
      background: #f3f4f6;
      vertical-align: middle;
    }

    .items-table td {
      padding: 5px 5px;
      vertical-align: top;
      font-size: 8.5pt;
    }

    /*
        |--------------------------------------------------------------------------
        | COLUMN WIDTHS
        |--------------------------------------------------------------------------
        */

    .col-no {
      width: 8%;
      text-align: center;
    }

    .col-description {
      width: 43%;
    }

    .col-qty {
      width: 10%;
      text-align: center;
    }

    .col-sat {
      width: 9%;
      text-align: center;
    }

    .col-unit {
      width: 15%;
      text-align: right;
    }

    .col-total {
      width: 15%;
      text-align: right;
    }

    /*
        |--------------------------------------------------------------------------
        | SECTION
        |--------------------------------------------------------------------------
        */

    .section-row td {
      padding: 6px 7px;
      background: #e5e7eb;
      font-weight: 800;
      text-transform: uppercase;
      font-size: 8.5pt;
    }

    /*
        |--------------------------------------------------------------------------
        | ITEM CONTENT
        |--------------------------------------------------------------------------
        */

    .item-description {
      white-space: pre-line;
      line-height: 1.4;
    }

    .item-notes {
      margin-top: 3px;
      color: #4b5563;
      font-size: 7.5pt;
      font-style: italic;
      white-space: pre-line;
    }

    .amount {
      white-space: nowrap;
      text-align: right;
    }

    /*
        |--------------------------------------------------------------------------
        | SUMMARY
        |--------------------------------------------------------------------------
        */

    .summary-wrapper {
      display: flex;
      justify-content: flex-end;
      margin-top: 12px;
    }

    .summary-table {
      width: 48%;
      border-collapse: collapse;
    }

    .summary-table td {
      padding: 4px 6px;
      font-size: 9pt;
    }

    .summary-table .summary-label {
      text-align: right;
      font-weight: 600;
    }

    .summary-table .summary-value {
      width: 42%;
      text-align: right;
      white-space: nowrap;
      font-weight: 600;
    }

    .summary-table .subtotal-row td {
      border-top: 1px solid #111827;
    }

    .summary-table .grand-total td {
      border-top: 2px solid #111827;
      border-bottom: 2px solid #111827;
      font-size: 10pt;
      font-weight: 800;
      padding-top: 6px;
      padding-bottom: 6px;
    }

    .summary-dpp {
      height: 25px;
    }

    /*
        |--------------------------------------------------------------------------
        | NOTES
        |--------------------------------------------------------------------------
        */

    .notes-section {
      margin-top: 14px;
    }

    .notes-title {
      margin: 0 0 4px;
      font-size: 9pt;
      font-weight: 800;
    }

    .notes-content {
      white-space: pre-line;
      font-size: 8.5pt;
      line-height: 1.5;
    }

    /*
        |--------------------------------------------------------------------------
        | SIGNATURE
        |--------------------------------------------------------------------------
        */

    .signature-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 28px;
      table-layout: fixed;
    }

    .signature-table td {
      width: 25%;
      padding: 0 5px;
      text-align: center;
      vertical-align: top;
      font-size: 8pt;
    }

    .signature-title {
      min-height: 18px;
      font-weight: 700;
    }

    .signature-space {
      height: 48px;
    }

    .signature-name {
      font-weight: 700;
    }

    .signature-date {
      margin-top: 2px;
      font-size: 7.5pt;
    }

    /*
        |--------------------------------------------------------------------------
        | FOOTER
        |--------------------------------------------------------------------------
        */

    .document-footer {
      margin-top: 18px;
      padding-top: 7px;
      border-top: 1px solid #9ca3af;
      text-align: center;
      color: #4b5563;
      font-size: 7pt;
      line-height: 1.4;
    }

    /*
        |--------------------------------------------------------------------------
        | PRINT
        |--------------------------------------------------------------------------
        */

    .print-toolbar {
      position: fixed;
      top: 18px;
      right: 18px;
      z-index: 1000;
      display: flex;
      gap: 8px;
    }

    .print-button {
      border: 0;
      border-radius: 6px;
      padding: 9px 14px;
      background: #2563eb;
      color: #ffffff;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
    }

    .print-button:hover {
      background: #1d4ed8;
    }

    .back-button {
      display: inline-flex;
      align-items: center;
      padding: 9px 14px;
      border-radius: 6px;
      background: #ffffff;
      color: #111827;
      text-decoration: none;
      font-size: 13px;
      font-weight: 700;
      border: 1px solid #d1d5db;
    }

    /*
        |--------------------------------------------------------------------------
        | PRINT MEDIA
        |--------------------------------------------------------------------------
        */

    @media print {

      html,
      body {
        background: #ffffff;
      }

      body {
        padding: 0;
      }

      .po-document {
        width: auto;
        min-height: auto;
        margin: 0;
        padding: 0;
        box-shadow: none;
      }

      .print-toolbar {
        display: none !important;
      }

      .items-table tr {
        break-inside: avoid;
        page-break-inside: avoid;
      }

      .section-row {
        break-after: avoid;
        page-break-after: avoid;
      }

      .summary-wrapper,
      .notes-section,
      .signature-table {
        break-inside: avoid;
        page-break-inside: avoid;
      }

      a {
        color: inherit;
        text-decoration: none;
      }
    }

    /*
        |--------------------------------------------------------------------------
        | SCREEN RESPONSIVE
        |--------------------------------------------------------------------------
        */

    @media screen and (max-width: 900px) {
      body {
        padding: 0;
      }

      .po-document {
        width: 100%;
        min-height: auto;
        padding: 24px;
      }

      .company-header {
        flex-direction: column;
      }

      .document-title,
      .document-subtitle {
        text-align: left;
      }

      .summary-table {
        width: 100%;
      }
    }
  </style>
</head>

<body>

  {{-- TOOLBAR --}}
  <div class="print-toolbar">
    <a href="{{ url()->previous() }}" class="back-button">
      ← Kembali
    </a>

    <button type="button" class="print-button" onclick="window.print()">
      Print / Save PDF
    </button>
  </div>

  <main class="po-document">

    {{-- =========================================================
            COMPANY HEADER
        ========================================================== --}}
    <header class="company-header">

      <div class="company-brand">

        <img src="{{ asset('images/logo-tkj.png') }}" alt="Logo {{ $companyName }}" class="company-logo">

        <div class="company-info">

          <h1 class="company-name">
            {{ $companyName }}
          </h1>

          <p class="company-address">
            {{ $companyAddress }}
          </p>

          <p class="company-contact">
            Telp : {{ $companyPhone }}
          </p>

          <p class="company-contact">
            Email : {{ $companyEmail }}
          </p>

          <p class="company-contact">
            Website : tanjungkaryajaya.co.id
          </p>

        </div>

      </div>

      <div>
        <h2 class="document-title">
          PURCHASE ORDER
        </h2>

        <p class="document-subtitle">
          PT. TANJUNG KARYA JAYA
        </p>
      </div>

    </header>

    <hr class="header-rule">

    {{-- =========================================================
            PO INFORMATION
        ========================================================== --}}
    <table class="po-meta">
      <tr>
        <td class="label">
          Vendor
        </td>

        <td class="separator">
          :
        </td>

        <td class="value">
          {{ $po->customer ?: '-' }}
        </td>

        <td class="right-label">
          PO Number
        </td>

        <td class="separator">
          :
        </td>

        <td class="value">
          {{ $po->po_number ?: '-' }}
        </td>
      </tr>

      <tr>
        <td class="label">
          Project
        </td>

        <td class="separator">
          :
        </td>

        <td class="value">
          {{ $po->project?->nama_project ?? ($po->project_id ?? '-') }}
        </td>

        <td class="right-label">
          Date
        </td>

        <td class="separator">
          :
        </td>

        <td class="value">
          {{ $poDate }}
        </td>
      </tr>

      <tr>
        <td class="label">
          Location
        </td>

        <td class="separator">
          :
        </td>

        <td class="value">
          {{ $po->location ?: '-' }}
        </td>

        <td class="right-label">
          Quotation
        </td>

        <td class="separator">
          :
        </td>

        <td class="value">
          {{ $po->quotation_no ?: '-' }}
        </td>
      </tr>

      <tr>
        <td class="label">
          PIC
        </td>

        <td class="separator">
          :
        </td>

        <td class="value">
          {{ $po->pic ?: '-' }}
        </td>

      </tr>
    </table>

    {{-- =========================================================
            ITEM TABLE
        ========================================================== --}}
    <table class="items-table">

      <thead>
        <tr>
          <th class="col-no">
            No.
          </th>

          <th class="col-description">
            Description
          </th>

          <th class="col-qty">
            Qty
          </th>

          <th class="col-sat">
            SAT
          </th>

          <th class="col-unit">
            Unit Price (Rp)
          </th>

          <th class="col-total">
            Amount (Rp)
          </th>
        </tr>
      </thead>

      <tbody>

        @php
          $displayNo = 0;
          $currentSection = null;
        @endphp

        @forelse ($items as $item)
          {{-- SECTION HEADER --}}
          @if (filled($item->section) && $item->section !== $currentSection)
            @php
              $currentSection = $item->section;
            @endphp

            <tr class="section-row">
              <td colspan="6">
                {{ $item->section }}
              </td>
            </tr>
          @endif

          @php
            $displayNo++;
          @endphp

          <tr>

            <td class="col-no">
              {{ $displayNo }}
            </td>

            <td class="col-description">

              <div class="item-description">
                {{ $item->description }}
              </div>

              @if (filled($item->notes))
                <div class="item-notes">
                  {{ $item->notes }}
                </div>
              @endif

            </td>

            <td class="col-qty">
              {{ $qty($item->quantity) }}
            </td>

            <td class="col-sat">
              {{ $item->sat ?: '-' }}
            </td>

            <td class="amount">
              {{ number_format((float) $item->unit_price, 0, ',', '.') }}
            </td>

            <td class="amount">
              {{ number_format((float) $item->total_price, 0, ',', '.') }}
            </td>

          </tr>

        @empty

          <tr>
            <td colspan="6" style="text-align:center; padding:20px;">
              Belum ada item Purchase Order.
            </td>
          </tr>
        @endforelse

      </tbody>

    </table>

    {{-- =========================================================
            SUMMARY
        ========================================================== --}}
    <div class="summary-wrapper">

      <table class="summary-table">

        <tr class="subtotal-row">
          <td class="summary-label">
            SUBTOTAL
          </td>

          <td class="summary-value">
            {{ $money($subtotal) }}
          </td>
        </tr>

        @if ($ppnEnabled)
          <tr>
            <td class="summary-label">
              PPN {{ rtrim(rtrim(number_format($ppnDisplayPercentage, 2, ',', '.'), '0'), ',') }}%
            </td>

            <td class="summary-value">
              {{ $money($ppnAmount) }}
            </td>
          </tr>

          <tr>
            <td class="summary-label">
              SUBTOTAL + PPN
            </td>

            <td class="summary-value">
              {{ $money($subtotalAfterPpn) }}
            </td>
          </tr>
        @endif

        @if ($discountEnabled)
          <tr>
            <td class="summary-label">
              DISKON {{ rtrim(rtrim(number_format($discountPercentage, 2, ',', '.'), '0'), ',') }}%
            </td>

            <td class="summary-value">
              - {{ $money($discountAmount) }}
            </td>
          </tr>
        @endif

        {{-- DPP if necessary --}}
        <tr class="summary-dpp">
          <td class="summary-label"></td>
          
          <td class="summary-value"></td>
        </tr>

        <tr class="grand-total">
          <td class="summary-label">
            GRAND TOTAL
          </td>

          <td class="summary-value">
            {{ $money($grandTotal) }}
          </td>
        </tr>

        

      </table>

    </div>

    {{-- =========================================================
            GLOBAL NOTES
        ========================================================== --}}
    @if (filled($po->notes))
      <section class="notes-section">

        <h3 class="notes-title">
          Notes :
        </h3>

        <div class="notes-content">
          {{ $po->notes }}
        </div>

      </section>
    @endif

    {{-- =========================================================
            SIGNATURE
        ========================================================== --}}
    <table class="signature-table">

      <tr>

        <td>
          <div class="signature-title">
            Prepared By
          </div>

          <div class="signature-space"></div>

          <div class="signature-name">
            ______________________
          </div>

          <div class="signature-date">
            Date : {{ $poDate }}
          </div>
        </td>

        <td>
          <div class="signature-title">
            Reviewed By
          </div>

          <div class="signature-space"></div>

          <div class="signature-name">
            ______________________
          </div>

          <div class="signature-date">
            Date : {{ $poDate }}
          </div>
        </td>

        <td>
          <div class="signature-title">
            Approved By
          </div>

          <div class="signature-space"></div>

          <div class="signature-name">
            ______________________
          </div>

          <div class="signature-date">
            Date : {{ $poDate }}
          </div>
        </td>

        <td>
          <div class="signature-title">
            Subcontractor / Vendor
          </div>

          <div class="signature-space"></div>

          <div class="signature-name">
            ______________________
          </div>

          <div class="signature-date">
            Date : {{ $poDate }}
          </div>
        </td>

      </tr>

    </table>

    {{-- =========================================================
            FOOTER
        ========================================================== --}}
    <footer class="document-footer">

      <strong>{{ $companyName }}</strong><br>

      {{ $companyAddress }}<br>

      Telp : {{ $companyPhone }}
      &nbsp; | &nbsp;
      Email : {{ $companyEmail }}

    </footer>

  </main>

</body>

</html>
