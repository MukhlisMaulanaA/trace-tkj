<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">

  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>
    Purchase Order - {{ $purchaseOrder->po_number }}
  </title>

  <style>
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
      font-size: 11px;
    }

    .toolbar {
      position: sticky;
      top: 0;
      z-index: 1000;

      display: flex;
      justify-content: center;
      gap: 10px;

      padding: 12px;

      background: #111827;
    }

    .toolbar button,
    .toolbar a {
      border: 0;
      border-radius: 6px;
      padding: 9px 16px;

      font-size: 12px;
      font-weight: 600;

      text-decoration: none;
      cursor: pointer;
    }

    .btn-print {
      background: #2563eb;
      color: white;
    }

    .btn-back {
      background: #374151;
      color: white;
    }

    .document-wrapper {
      padding: 30px 0;
    }

    .document {
      width: 210mm;
      min-height: 297mm;

      margin: 0 auto;
      padding: 15mm 15mm 18mm;

      background: white;

      box-shadow:
        0 10px 30px rgba(0, 0, 0, 0.12);
    }

    /* =========================================================
           HEADER
        ========================================================= */

    .header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;

      padding-bottom: 12px;

      border-bottom: 2px solid #111827;
    }

    .company {
      display: flex;
      align-items: flex-start;
      gap: 12px;
    }

    .company-logo {
      width: 55px;
      height: 55px;

      object-fit: contain;
    }

    .company-name {
      margin: 0;

      font-size: 17px;
      font-weight: 700;

      text-transform: uppercase;
    }

    .company-address {
      margin-top: 4px;

      max-width: 330px;

      font-size: 9px;
      line-height: 1.5;

      color: #4b5563;
    }

    .document-title {
      text-align: right;
    }

    .document-title h1 {
      margin: 0;

      font-size: 20px;
      font-weight: 800;

      letter-spacing: 0.5px;
    }

    .document-title .po-number {
      margin-top: 5px;

      font-size: 12px;
      font-weight: 700;
    }

    .document-title .po-date {
      margin-top: 3px;

      font-size: 10px;
      color: #4b5563;
    }

    /* =========================================================
           INFORMATION
        ========================================================= */

    .info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;

      gap: 25px;

      margin-top: 16px;
      margin-bottom: 18px;
    }

    .info-block {
      border: 1px solid #d1d5db;
    }

    .info-title {
      padding: 6px 9px;

      background: #f3f4f6;

      border-bottom: 1px solid #d1d5db;

      font-size: 9px;
      font-weight: 700;

      text-transform: uppercase;
    }

    .info-body {
      padding: 8px 9px;
    }

    .info-row {
      display: grid;
      grid-template-columns: 105px 1fr;

      margin-bottom: 4px;
    }

    .info-row:last-child {
      margin-bottom: 0;
    }

    .info-label {
      color: #6b7280;
    }

    .info-value {
      font-weight: 600;
    }

    /* =========================================================
           ITEMS TABLE
        ========================================================= */

    .items-table {
      width: 100%;

      border-collapse: collapse;

      margin-top: 8px;
    }

    .items-table th,
    .items-table td {
      border: 1px solid #9ca3af;

      padding: 7px 6px;

      vertical-align: top;
    }

    .items-table th {
      background: #f3f4f6;

      font-size: 9px;
      font-weight: 700;

      text-align: center;
      text-transform: uppercase;
    }

    .items-table td {
      font-size: 10px;
    }

    .col-no {
      width: 30px;
      text-align: center;
    }

    .col-description {
      width: auto;
    }

    .col-qty {
      width: 50px;
      text-align: center;
    }

    .col-sat {
      width: 48px;
      text-align: center;
    }

    .col-price {
      width: 95px;
      text-align: right;
    }

    .col-total {
      width: 105px;
      text-align: right;
    }

    .section-row td {
      padding: 6px 7px;

      background: #e5e7eb;

      font-weight: 700;
      font-size: 9px;

      text-transform: uppercase;
    }

    .item-notes {
      margin-top: 3px;

      font-size: 8px;
      font-style: italic;

      color: #6b7280;
    }

    /* =========================================================
           SUMMARY
        ========================================================= */

    .summary-wrapper {
      display: flex;
      justify-content: flex-end;

      margin-top: 12px;
    }

    .summary {
      width: 285px;
    }

    .summary-row {
      display: flex;
      justify-content: space-between;

      padding: 5px 8px;

      border-bottom: 1px solid #e5e7eb;
    }

    .summary-label {
      color: #4b5563;
    }

    .summary-value {
      font-weight: 600;
      text-align: right;
    }

    .summary-total {
      display: flex;
      justify-content: space-between;

      margin-top: 3px;
      padding: 9px 8px;

      background: #111827;

      color: white;

      font-size: 12px;
      font-weight: 800;
    }

    /* =========================================================
           NOTES
        ========================================================= */

    .notes {
      margin-top: 18px;

      border: 1px solid #d1d5db;
    }

    .notes-title {
      padding: 6px 8px;

      background: #f3f4f6;

      border-bottom: 1px solid #d1d5db;

      font-weight: 700;
      font-size: 9px;

      text-transform: uppercase;
    }

    .notes-content {
      min-height: 45px;

      padding: 8px;

      white-space: pre-line;

      font-size: 9px;
      line-height: 1.5;
    }

    /* =========================================================
           SIGNATURE
        ========================================================= */

    .signature-wrapper {
      display: grid;
      grid-template-columns: 1fr 1fr;

      gap: 50px;

      margin-top: 35px;
    }

    .signature {
      text-align: center;
    }

    .signature-title {
      font-size: 9px;
      font-weight: 600;
    }

    .signature-space {
      height: 70px;
    }

    .signature-name {
      padding-top: 5px;

      border-top: 1px solid #111827;

      font-size: 10px;
      font-weight: 700;
    }

    /* =========================================================
           FOOTER
        ========================================================= */

    .document-footer {
      margin-top: 20px;

      padding-top: 7px;

      border-top: 1px solid #d1d5db;

      text-align: center;

      font-size: 8px;
      color: #6b7280;
    }

    /* =========================================================
           PRINT
        ========================================================= */

    @page {
      size: A4;
      margin: 0;
    }

    @media print {

      html,
      body {
        background: white;
      }

      .no-print {
        display: none !important;
      }

      .toolbar {
        display: none !important;
      }

      .document-wrapper {
        padding: 0;
      }

      .document {
        width: 210mm;
        min-height: 297mm;

        margin: 0;
        padding: 15mm 15mm 18mm;

        box-shadow: none;
      }

      thead {
        display: table-header-group;
      }

      tfoot {
        display: table-footer-group;
      }

      tr {
        page-break-inside: avoid;
        break-inside: avoid;
      }

      .signature-wrapper {
        page-break-inside: avoid;
        break-inside: avoid;
      }

      .notes {
        page-break-inside: avoid;
        break-inside: avoid;
      }
    }
  </style>
</head>

<body>

  {{-- =========================================================
         TOOLBAR
    ========================================================== --}}

  <div class="toolbar no-print">

    <a href="{{ url()->previous() }}" class="btn-back">
      ← Kembali
    </a>

    <button type="button" class="btn-print" onclick="window.print()">
      Save as PDF / Print
    </button>

  </div>


  {{-- =========================================================
         DOCUMENT
    ========================================================== --}}

  <div class="document-wrapper">

    <main class="document">

      {{-- HEADER --}}
      <header class="header">

        <div class="company">

          {{-- Ganti path logo sesuai logo perusahaan --}}
          @if (file_exists(public_path('images/logo.png')))
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="company-logo">
          @endif

          <div>
            <h2 class="company-name">
              {{ config('app.name') }}
            </h2>

            <div class="company-address">
              Alamat perusahaan<br>
              Telepon / Email / Website
            </div>
          </div>

        </div>

        <div class="document-title">

          <h1>PURCHASE ORDER</h1>

          <div class="po-number">
            {{ $purchaseOrder->po_number }}
          </div>

          <div class="po-date">
            {{ $purchaseOrder->po_date?->format('d F Y') ?? '-' }}
          </div>

        </div>

      </header>


      {{-- INFORMATION --}}
      <section class="info-grid">

        <div class="info-block">

          <div class="info-title">
            Supplier / Customer
          </div>

          <div class="info-body">

            <div class="info-row">
              <span class="info-label">
                Customer
              </span>

              <span class="info-value">
                {{ $purchaseOrder->customer ?: '-' }}
              </span>
            </div>

            <div class="info-row">
              <span class="info-label">
                Lokasi
              </span>

              <span class="info-value">
                {{ $purchaseOrder->location ?: '-' }}
              </span>
            </div>

            <div class="info-row">
              <span class="info-label">
                PIC
              </span>

              <span class="info-value">
                {{ $purchaseOrder->pic ?: '-' }}
              </span>
            </div>

          </div>

        </div>


        <div class="info-block">

          <div class="info-title">
            Referensi
          </div>

          <div class="info-body">

            <div class="info-row">
              <span class="info-label">
                Project
              </span>

              <span class="info-value">
                {{ $purchaseOrder->project?->id ?? '-' }}
              </span>
            </div>

            <div class="info-row">
              <span class="info-label">
                Nama Project
              </span>

              <span class="info-value">
                {{ $purchaseOrder->project?->nama_project ?? '-' }}
              </span>
            </div>

            <div class="info-row">
              <span class="info-label">
                Quotation
              </span>

              <span class="info-value">
                {{ $purchaseOrder->quotation_no ?: '-' }}
              </span>
            </div>

          </div>

        </div>

      </section>


      {{-- ITEMS --}}
      @php
        $subtotal = (float) ($purchaseOrder->subtotal ?? 0);

        $ppnEnabled = (bool) ($purchaseOrder->ppn_enabled ?? false);

        $ppnAmount = $ppnEnabled ? (float) ($purchaseOrder->ppn_amount ?? round($subtotal * 0.11)) : 0;

        $totalAfterPpn = $subtotal + $ppnAmount;

        $discountEnabled = (bool) ($purchaseOrder->discount_enabled ?? false);

        $discountPercent = (float) ($purchaseOrder->discount_percent ?? 0);

        $discountAmount = $discountEnabled
            ? (float) ($purchaseOrder->discount_amount ?? round($totalAfterPpn * ($discountPercent / 100)))
            : 0;

        $finalTotal = max(0, $totalAfterPpn - $discountAmount);
      @endphp


      <table class="items-table">

        <thead>

          <tr>
            <th class="col-no">No.</th>
            <th class="col-description">Description</th>
            <th class="col-qty">Qty</th>
            <th class="col-sat">SAT</th>
            <th class="col-price">Unit Price</th>
            <th class="col-total">Amount</th>
          </tr>

        </thead>

        <tbody>

          @php
            $currentSection = null;
            $displayNumber = 0;
          @endphp

          @forelse ($purchaseOrder->items as $item)

            @if ($item->section !== $currentSection)
              @php
                $currentSection = $item->section;
              @endphp

              @if ($currentSection)
                <tr class="section-row">
                  <td colspan="6">
                    {{ $currentSection }}
                  </td>
                </tr>
              @endif
            @endif

            @php
              $displayNumber++;
            @endphp

            <tr>

              <td class="col-no">
                {{ $displayNumber }}
              </td>

              <td class="col-description">

                <strong>
                  {{ $item->description }}
                </strong>

                @if ($item->notes)
                  <div class="item-notes">
                    {{ $item->notes }}
                  </div>
                @endif

              </td>

              <td class="col-qty">
                {{ rtrim(rtrim(number_format((float) $item->quantity, 2, ',', '.'), '0'), ',') }}
              </td>

              <td class="col-sat">
                {{ $item->sat ?: '-' }}
              </td>

              <td class="col-price">
                Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}
              </td>

              <td class="col-total">
                Rp {{ number_format((float) $item->total_price, 0, ',', '.') }}
              </td>

            </tr>

          @empty

            <tr>
              <td colspan="6" style="text-align:center;">
                Belum ada item Purchase Order.
              </td>
            </tr>

          @endforelse

        </tbody>

      </table>


      {{-- SUMMARY --}}
      <div class="summary-wrapper">

        <div class="summary">

          <div class="summary-row">
            <span class="summary-label">
              Subtotal
            </span>

            <span class="summary-value">
              Rp {{ number_format($subtotal, 0, ',', '.') }}
            </span>
          </div>


          @if ($ppnEnabled)
            <div class="summary-row">

              <span class="summary-label">
                PPN 12%
              </span>

              <span class="summary-value">
                Rp {{ number_format($ppnAmount, 0, ',', '.') }}
              </span>

            </div>
          @endif


          <div class="summary-row">

            <span class="summary-label">
              Total Setelah PPN
            </span>

            <span class="summary-value">
              Rp {{ number_format($totalAfterPpn, 0, ',', '.') }}
            </span>

          </div>


          @if ($discountEnabled)
            <div class="summary-row">

              <span class="summary-label">
                Diskon {{ number_format($discountPercent, 2, ',', '.') }}%
              </span>

              <span class="summary-value">
                − Rp {{ number_format($discountAmount, 0, ',', '.') }}
              </span>

            </div>
          @endif


          <div class="summary-total">

            <span>
              TOTAL AKHIR
            </span>

            <span>
              Rp {{ number_format($finalTotal, 0, ',', '.') }}
            </span>

          </div>

        </div>

      </div>


      {{-- NOTES --}}
      @if ($purchaseOrder->notes)
        <section class="notes">

          <div class="notes-title">
            Notes
          </div>

          <div class="notes-content">
            {{ $purchaseOrder->notes }}
          </div>

        </section>
      @endif


      {{-- SIGNATURE --}}
      <section class="signature-wrapper">

        <div class="signature">

          <div class="signature-title">
            Prepared By
          </div>

          <div class="signature-space"></div>

          <div class="signature-name">
            {{ $purchaseOrder->pic ?: '________________________' }}
          </div>

        </div>


        <div class="signature">

          <div class="signature-title">
            Approved By
          </div>

          <div class="signature-space"></div>

          <div class="signature-name">
            ________________________
          </div>

        </div>

      </section>


      {{-- FOOTER --}}
      <footer class="document-footer">

        Purchase Order —
        {{ $purchaseOrder->po_number }}

      </footer>

    </main>

  </div>

</body>

</html>
