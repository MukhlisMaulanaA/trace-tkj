@php
  /** @var \App\Models\ProjectSummary $record */
  $project = $record->project;
  $materialExpenditures = $record
      ->expenditures()
      ->where('type', \App\Models\ProjectExpenditure::TYPE_MATERIAL)
      ->orderBy('expenditure_date')
      ->get();
  $labourExpenditures = $record
      ->expenditures()
      ->where('type', \App\Models\ProjectExpenditure::TYPE_LABOUR)
      ->orderBy('expenditure_date')
      ->get();

  $companyName = 'PT. TANJUNG KARYA JAYA';
  $companyAddress =
      'Perumahan Bumi Anugrah Sejahtera Blok B4 - No.3, ' .
      'Rt.009 / Rw.013, Kelurahan Kebalen, ' .
      'Kec. Babelan - Bekasi, Jawa Barat';
  $companyPhone = '0811-1020-770 - 0856-1539-431';
  $companyEmail = 'officetkj@tanjungkaryajaya.co.id / admin@tanjungkaryajaya.co.id';

  $money = static fn($value): string => 'Rp' . number_format((float) ($value ?? 0), 0, ',', '.');
  $date = static fn($value): string => $value?->format('d/m/Y') ?? '-';
  $projectTitle = $project?->nama_project ?: $record->project_name ?: 'PROJECT';
  $poNumbers = $record->po_numbers ?: '-';
  $owner = $project?->kustomer ?: $record->owner_customer ?: '-';
  $contractValue = (float) $record->contract_value;
  $finalContractValue = (float) $record->final_contract_value;
  $materialTotal = (float) $record->material_expenditure;
  $labourTotal = (float) $record->labour_expenditure;
  $mlTotal = (float) $record->ml_expenditure;
  $finalProfit = (float) $record->final_profit;
  $remainingBudget = (float) $record->remaining_budget;
  $balance = (float) $record->balance;
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Rekap Pengeluaran Project - {{ $projectTitle }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    @page {
      size: A4 landscape;
      margin: 8mm 8mm 6mm;
    }

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
      font-size: 8.5pt;
      line-height: 1.15;
    }

    body {
      padding: 18px 0;
    }

    .document {
      width: 297mm;
      min-height: 210mm;
      margin: 0 auto;
      padding: 7mm 8mm 5mm;
      background: #fff;
      box-shadow: 0 3px 20px rgba(0, 0, 0, .12);
    }

    .company-header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 18px;
      margin-bottom: 5px;
    }

    .company-brand {
      display: flex;
      align-items: flex-start;
      gap: 9px;
      min-width: 0;
    }

    .company-logo {
      width: 42px;
      height: 42px;
      object-fit: contain;
      flex: 0 0 42px;
    }

    .company-name {
      margin: 0 0 2px;
      font-size: 12pt;
      line-height: 1.1;
      font-weight: 800;
    }

    .company-address,
    .company-contact {
      margin: 0;
      font-size: 6.5pt;
      line-height: 1.25;
      color: #374151;
    }

    .document-title {
      margin: 0;
      text-align: right;
      font-size: 15pt;
      line-height: 1;
      font-weight: 800;
      letter-spacing: .5px;
    }

    .document-subtitle {
      margin: 4px 0 0;
      text-align: right;
      font-size: 6.5pt;
      color: #6b7280;
    }

    .header-rule {
      border: 0;
      border-top: 1.5px solid #111827;
      margin: 5px 0 7px;
    }

    .title {
      margin: 0 0 6px;
      text-align: center;
      font-size: 15pt;
      line-height: 1;
      font-weight: 800;
      text-transform: uppercase;
    }

    .metrics {
      display: grid;
      grid-template-columns: 1.45fr 1.45fr 1.45fr 1.45fr;
      gap: 8px;
      margin-bottom: 7px;
    }

    .metric-group {
      display: grid;
      grid-template-columns: 1fr;
    }

    .metric {
      min-height: 25px;
      border: 1px solid #5b5b5b;
      text-align: center;
    }

    .metric-label {
      padding: 4px 5px;
      background: #f4c7a7;
      font-size: 10pt;
      font-weight: 800;
      text-transform: uppercase;
    }

    .metric-value {
      padding: 4px 5px;
      font-size: 9pt;
      font-weight: 700;
      white-space: nowrap;
    }

    .metric-value.green {
      background: #00b050;
    }

    .workspace {
      display: grid;
      grid-template-columns: minmax(0, 1.55fr) minmax(0, 1fr);
      gap: 9px;
      align-items: start;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
    }

    th,
    td {
      border: 1px solid #666;
    }

    th {
      padding: 4px 5px;
      background: #f4c7a7;
      font-size: 8.5pt;
      font-weight: 800;
      text-align: center;
      text-transform: uppercase;
    }

    td {
      padding: 3px 5px;
      height: 18px;
      vertical-align: middle;
      font-size: 8pt;
    }

    .material-table .no {
      width: 6%;
      text-align: center;
    }

    .material-table .description {
      width: 42%;
    }

    .material-table .amount {
      width: 23%;
      text-align: right;
      white-space: nowrap;
    }

    .material-table .date {
      width: 29%;
      text-align: center;
      white-space: nowrap;
    }

    .labour-table .no {
      width: 11%;
      text-align: center;
    }

    .labour-table .description {
      width: 39%;
    }

    .labour-table .amount {
      width: 25%;
      text-align: right;
      white-space: nowrap;
    }

    .labour-table .date {
      width: 25%;
      text-align: center;
      white-space: nowrap;
    }

    .section-heading {
      margin: 0;
      padding: 4px 6px;
      border: 1px solid #666;
      border-bottom: 0;
      background: #f4c7a7;
      text-align: center;
      font-size: 9pt;
      font-weight: 800;
      text-transform: uppercase;
    }

    .right-stack {
      display: grid;
      grid-template-columns: minmax(0, 1fr) 104px;
      gap: 9px;
      margin-bottom: 8px;
    }

    .right-stack .metric-label {
      font-size: 9pt;
    }

    .right-stack .metric-value {
      font-size: 8.5pt;
    }

    .profit-box .metric-value {
      min-height: 49px;
      padding-top: 16px;
      font-size: 15pt;
    }

    .total-row td {
      background: #ffff00;
      font-size: 9pt;
      font-weight: 800;
    }

    .total-row td:last-child {
      text-align: right;
    }

    .footer {
      margin-top: 8px;
      padding-top: 4px;
      border-top: 1px solid #9ca3af;
      text-align: center;
      color: #4b5563;
      font-size: 6.5pt;
      line-height: 1.25;
    }

    .toolbar {
      position: fixed;
      top: 14px;
      right: 14px;
      z-index: 10;
      display: flex;
      gap: 6px;
    }

    .toolbar a,
    .toolbar button {
      border: 1px solid #d1d5db;
      border-radius: 5px;
      padding: 7px 10px;
      background: #fff;
      color: #111827;
      font-size: 12px;
      font-weight: 700;
      text-decoration: none;
      cursor: pointer;
    }

    .toolbar button {
      border-color: #2563eb;
      background: #2563eb;
      color: #fff;
    }

    @media print {

      @page {
        size: A4 landscape;
        margin: 0;
      }

      html,
      body {
        background: #fff;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }

      body {
        padding: 0;
      }

      .document {
        width: auto;
        min-height: auto;
        margin: 0;  
        padding: 8mm;
        box-shadow: none;
      }

      .metric-label,
      th {
        background: #f4c7a7 !important;
      }

      .metric-value.green {
        background: #00b050 !important;
      }

      .total-row td {
        background: #ffff00 !important;
      }

      .toolbar {
        display: none;
      }

      tr {
        break-inside: avoid;
        page-break-inside: avoid;
      }
    }

    @media screen and (max-width: 1100px) {
      body {
        padding: 0;
      }

      .document {
        width: 100%;
        min-height: auto;
        padding: 24px;
      }

      .metrics,
      .workspace {
        grid-template-columns: 1fr;
      }

      .right-stack {
        grid-template-columns: 1fr;
      }

      .company-header {
        flex-direction: column;
      }

      .document-title,
      .document-subtitle {
        text-align: left;
      }

      .toolbar {
        position: static;
        padding: 10px;
        background: #e5e7eb;
      }
    }
  </style>
</head>

<body>
  <div class="toolbar">
    <a href="{{ url()->previous() }}">Kembali</a>
    <button type="button" onclick="window.print()">Print / Save PDF</button>
  </div>

  <main class="document">
    <header class="company-header">
      <div class="company-brand">
        <img src="{{ asset('images/logo-tkj.png') }}" alt="Logo {{ $companyName }}" class="company-logo">
        <div>
          <h1 class="company-name">{{ $companyName }}</h1>
          <p class="company-address">{{ $companyAddress }}</p>
          <p class="company-contact">Telp : {{ $companyPhone }}</p>
          <p class="company-contact">Email : {{ $companyEmail }}</p>
          <p class="company-contact">Website : tanjungkaryajaya.co.id</p>
        </div>
      </div>
      <div>
        <h2 class="document-title">REKAPITULASI PROJECT</h2>
        <p class="document-subtitle">PT. TANJUNG KARYA JAYA</p>
      </div>
    </header>

    <hr class="header-rule">
    <h2 class="title">REKAP PENGELUARAN PROJECT {{ $projectTitle }}</h2>

    <section class="metrics">
      <div class="metric-group">
        <div class="metric">
          <div class="metric-label">Name of PIC Project</div>
          <div class="metric-value">{{ $project?->pic ?: '-' }}</div>
        </div>
        <div class="metric">
          <div class="metric-label">Number PO</div>
          <div class="metric-value">{{ $poNumbers }}</div>
        </div>
      </div>
      <div class="metric-group">
        <div class="metric">
          <div class="metric-label">Owner</div>
          <div class="metric-value">{{ $owner }}</div>
        </div>
        <div class="metric">
          <div class="metric-label">PPH {{ number_format(\App\Models\ProjectSummary::PPH_RATE * 100, 2) }}%</div>
          <div class="metric-value">{{ $money($record->pph_amount) }}</div>
        </div>
      </div>
      <div class="metric-group">
        <div class="metric">
          <div class="metric-label">Nilai Kontrak</div>
          <div class="metric-value">{{ $money($contractValue) }}</div>
        </div>
        <div class="metric">
          <div class="metric-label">Final Profit</div>
          <div class="metric-value">{{ $money($finalProfit) }}</div>
        </div>
      </div>
      <div class="metric-group">
        <div class="metric">
          <div class="metric-label">Final Kontrak</div>
          <div class="metric-value">{{ $money($finalContractValue) }}</div>
        </div>
        <div class="metric">
          <div class="metric-label">Balance</div>
          <div class="metric-value">{{ $money($balance) }}</div>
        </div>
      </div>
    </section>

    <section class="workspace">
      <div>
        <table class="material-table">
          <thead>
            <tr>
              <th class="no">No.</th>
              <th class="description">List Material</th>
              <th class="amount">Harga Material</th>
              <th class="date">Tanggal Pembelian</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($materialExpenditures as $expenditure)
              <tr>
                <td class="no">{{ $loop->iteration }}</td>
                <td class="description">{{ $expenditure->description }}</td>
                <td class="amount">{{ number_format((float) $expenditure->amount, 0, ',', '.') }}</td>
                <td class="date">{{ $date($expenditure->expenditure_date) }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="4" style="text-align:center;">Belum ada pengeluaran material.</td>
              </tr>
            @endforelse
          </tbody>
          <tfoot>
            <tr class="total-row">
              <td colspan="2">TOTAL MATERIAL</td>
              <td colspan="2">{{ number_format($materialTotal, 0, ',', '.') }}</td>
            </tr>
          </tfoot>
        </table>
      </div>

      <div>
        <div class="right-stack">
          <div>
            <div class="metric">
              <div class="metric-label">Sisa Budget</div>
              <div class="metric-value green">{{ $money($remainingBudget) }}</div>
            </div>
            <div class="metric" style="margin-top:8px;">
              <div class="metric-label">Pengeluaran M + L</div>
              <div class="metric-value">{{ $money($mlTotal) }}</div>
            </div>
          </div>
          <div class="metric profit-box">
            <div class="metric-label">Profit</div>
            <div class="metric-value">{{ number_format((float) $record->profit_percentage, 0) }}%</div>
          </div>
        </div>

        <h3 class="section-heading">Labour</h3>
        <table class="labour-table">
          <thead>
            <tr>
              <th class="no">No.</th>
              <th class="description">Labour</th>
              <th class="amount">Nilai</th>
              <th class="date">Periode</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($labourExpenditures as $expenditure)
              <tr>
                <td class="no">{{ $loop->iteration }}</td>
                <td class="description">{{ $expenditure->description }}</td>
                <td class="amount">{{ number_format((float) $expenditure->amount, 0, ',', '.') }}</td>
                <td class="date">{{ $date($expenditure->expenditure_date) }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="4" style="text-align:center;">Belum ada pengeluaran labour.</td>
              </tr>
            @endforelse
          </tbody>
          <tfoot>
            <tr class="total-row">
              <td colspan="2">TOTAL LABOUR</td>
              <td colspan="2">{{ number_format($labourTotal, 0, ',', '.') }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </section>

    <footer class="footer">
      <strong>{{ $companyName }}</strong><br>
      {{ $companyAddress }}<br>
      Telp : {{ $companyPhone }} &nbsp; | &nbsp; Email : {{ $companyEmail }}
    </footer>
  </main>
</body>

</html>
