<x-filament-panels::page>
  @php
    $data = $this->dashboardData();
  @endphp

  <div class="trace-dashboard">
    <header class="trace-dashboard__header">
      <div>
        <div class="trace-dashboard__eyebrow"><span class="trace-dashboard__pulse"></span> TRACE TKJ / OVERVIEW</div>
        <h1>Monitoring Project</h1>
        <p>Pantau project, PO, progress, dan kondisi finansial secara real-time.</p>
      </div>
    </header>

    <div class="trace-dashboard__toolbar">
      <div class="trace-dashboard__alert"><x-filament::icon
          icon="heroicon-m-exclamation-triangle" /><strong>{{ $data['attention']->count() }}</strong> perlu perhatian
      </div>
      <span class="trace-dashboard__toolbar-copy">{{ $data['sourceLabel'] }} · {{ $data['periodLabel'] }}</span>
      <div class="trace-dashboard__filters">
        @if ($this->canChooseSource())
          <select wire:model.live="source" aria-label="Sumber project">
            @foreach ($this->sourceOptions() as $value => $label)
              <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
          </select>
        @endif
        <select wire:model.live="period" aria-label="Periode dashboard">
          @foreach ($this->periodOptions() as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
          @endforeach
        </select>
        <button type="button" wire:click="refreshDashboard" wire:loading.attr="disabled" wire:target="refreshDashboard"
          class="trace-dashboard__refresh" title="Refresh dashboard" aria-label="Refresh dashboard">
          <x-filament::icon icon="heroicon-m-arrow-path" wire:loading.remove wire:target="refreshDashboard" />
          <x-filament::icon icon="heroicon-m-arrow-path" class="animate-spin" wire:loading
            wire:target="refreshDashboard" />
        </button>
      </div>
    </div>

    @if (!$data['hasData'])
      <div class="trace-dashboard__empty">Belum ada data project atau purchase order.</div>
    @endif

    <section class="trace-dashboard__kpis" aria-label="Ringkasan metrik">
      @foreach ($data['kpis'] as $kpi)
        <a href="{{ $kpi['url'] }}" class="trace-kpi trace-kpi--{{ $kpi['tone'] }}">
          <div class="trace-kpi__top"><span>{{ $kpi['label'] }}</span><x-filament::icon
              icon="heroicon-m-arrow-up-right" /></div>
          <strong>{{ $kpi['value'] }}</strong>
          <small class="trace-kpi__comparison trace-kpi__comparison--{{ $kpi['comparison']['direction'] }}">
            <span class="trace-kpi__delta">{{ $kpi['comparison']['direction'] === 'down' ? '↘' : '↗' }}</span>
            {{ $kpi['comparison']['label'] }}
          </small>
        </a>
      @endforeach
      <div class="trace-kpi trace-kpi--violet">
        <div class="trace-kpi__top"><span>Utilization</span><x-filament::icon icon="heroicon-m-chart-bar" /></div>
        <strong>{{ $data['utilization'] }}<small>%</small></strong>
        <small>budget terpakai</small>
      </div>
    </section>

    <div class="trace-dashboard__main-grid">
      <section class="trace-panel trace-panel--chart">
        <div class="trace-panel__heading">
          <div>
            <h2>Profit & Expenditure History</h2>
            <p>Kumulatif historis data untuk melihat perubahan dari waktu ke waktu</p>
          </div>
          <div class="trace-segmented" data-chart-ranges>
            <button class="is-active" type="button" data-chart-range="3m">3M</button>
            <button type="button" data-chart-range="6m">6M</button>
            <button type="button" data-chart-range="1y">1Y</button>
            <button type="button" data-chart-range="all">All</button>
          </div>
        </div>
        <div class="trace-legend">
          <span class="trace-legend__profit">Profit</span>
          <span class="trace-legend__expense">Expenditure</span>
          <span class="trace-legend__invoice">Nilai PO</span>
        </div>
        <div class="trace-chart" data-trace-chart>
          @foreach ($data['chartRanges'] as $range => $rangeData)
            @php
              $rangePoints = $rangeData['points'];
              $rangeMax = $rangeData['max'] > 0 ? $rangeData['max'] : 1; // Mencegah division by zero
              $axisLabels = $rangeData['axis'];
              $pointX = fn($index) => $rangePoints->count() > 1
                  ? ($index * 996) / ($rangePoints->count() - 1) + 12
                  : 510;

              $linePoints = fn($key) => $rangePoints
                  ->map(fn($point, $index) => $pointX($index) . ',' . (190 - ($point[$key] / $rangeMax) * 150))
                  ->implode(' ');

              // Menentukan lebar bar agar responsif terhadap jumlah data
              $barWidth = $rangePoints->count() > 12 ? 16 : 32;
            @endphp
            <div class="trace-chart__range" data-chart-panel="{{ $range }}"
              @if ($range !== '3m') hidden @endif>
              <div class="trace-chart__body">
                <div class="trace-chart__axis">
                  @foreach ($axisLabels as $axisLabel)
                    <span>{{ $axisLabel }}</span>
                  @endforeach
                </div>
                <svg viewBox="0 0 1020 230" role="img" aria-label="Grafik profit, expenditure dan nilai PO"
                  preserveAspectRatio="none">
                  <defs>
                    <linearGradient id="traceChartFill-{{ $range }}" x1="0" x2="0"
                      y1="0" y2="1">
                      <stop offset="0" stop-color="currentColor" stop-opacity=".22" />
                      <stop offset="1" stop-color="currentColor" stop-opacity="0" />
                    </linearGradient>
                  </defs>
                  @for ($line = 0; $line < 4; $line++)
                    <line x1="0" y1="{{ 40 + $line * 50 }}" x2="1020" y2="{{ 40 + $line * 50 }}"
                      class="trace-chart__grid" />
                  @endfor

                  <polyline points="0,190 {{ $linePoints('profit') }} 1020,190" class="trace-chart__area"
                    style="fill: url(#traceChartFill-{{ $range }})" />
                  <polyline points="{{ $linePoints('profit') }}" class="trace-chart__line trace-chart__line--profit" />
                  <polyline points="{{ $linePoints('expenditure') }}"
                    class="trace-chart__line trace-chart__line--expense" />

                  <!-- Hapus polyline PO di sini -->

                  @foreach ($rangePoints as $index => $point)
                    @php
                      $x = $pointX($index);
                      $poHeight = ($point['po'] / $rangeMax) * 150;
                      $poY = 190 - $poHeight;
                    @endphp

                    <!-- Bar untuk Nilai PO -->
                    <rect x="{{ $x - $barWidth / 2 }}" y="{{ $poY }}" width="{{ $barWidth }}"
                      height="{{ $poHeight }}" class="trace-chart__bar trace-chart__bar--po" data-chart-point
                      data-label="{{ $point['label'] }}" data-profit="{{ $point['profit'] }}"
                      data-expenditure="{{ $point['expenditure'] }}" data-po="{{ $point['po'] }}" rx="4"
                      ry="4" style="opacity: 0.8; cursor: pointer;" />

                    <!-- Titik untuk Profit & Expenditure -->
                    <circle cx="{{ $x }}" cy="{{ 190 - ($point['profit'] / $rangeMax) * 150 }}" r="4"
                      class="trace-chart__point trace-chart__point--profit" data-chart-point
                      data-label="{{ $point['label'] }}" data-profit="{{ $point['profit'] }}"
                      data-expenditure="{{ $point['expenditure'] }}" data-po="{{ $point['po'] }}" />

                    <circle cx="{{ $x }}" cy="{{ 190 - ($point['expenditure'] / $rangeMax) * 150 }}"
                      r="4" class="trace-chart__point trace-chart__point--expense" data-chart-point
                      data-label="{{ $point['label'] }}" data-profit="{{ $point['profit'] }}"
                      data-expenditure="{{ $point['expenditure'] }}" data-po="{{ $point['po'] }}" />
                  @endforeach
                </svg>
              </div>
              <div class="trace-chart__labels"
                style="grid-template-columns: repeat({{ max(1, $rangePoints->count()) }}, minmax(0, 1fr));">
                @foreach ($rangePoints as $point)
                  <span>{{ $point['label'] }}</span>
                @endforeach
              </div>
            </div>
          @endforeach
          <div class="trace-chart__tooltip" data-chart-tooltip hidden></div>
        </div>
      </section>

      <section class="trace-panel trace-panel--highlights">
        <div class="trace-panel__heading">
          <div>
            <h2>Project Highlights</h2>
            <p>Top performance this month</p>
          </div><a href="#attention">View all</a>
        </div>
        @forelse ($data['highlights'] as $item)
          <a href="{{ $item['url'] }}" class="trace-highlight trace-highlight--{{ $item['tone'] }}"><span
              class="trace-highlight__mark">●</span>
            <div>
              <small>{{ $item['label'] }}</small><strong>{{ $item['title'] }}</strong>
              <p>{{ $item['value'] }} · {{ $item['detail'] }}</p>
            </div>
          </a>
        @empty
          <div class="trace-dashboard__mini-empty">Belum ada project highlight.</div>
        @endforelse
      </section>
    </div>

    <section class="trace-panel trace-attention-panel" id="attention">
      <div class="trace-panel__heading">
        <div>
          <h2>Perlu Perhatian</h2>
          <p>Project atau PO tanpa aktivitas pembayaran/progress selama 30 hari</p>
        </div>
        <span class="trace-badge trace-badge--warning">{{ $data['attention']->count() }} item</span>
      </div>
      <div class="trace-attention-list">
        @forelse ($data['attention'] as $item)
          <a href="{{ $item['url'] }}" class="trace-attention-item">
            <span class="trace-attention-item__dot"></span>
            <span><strong>{{ $item['title'] }}</strong><small>{{ $item['detail'] }}</small></span>
            <x-filament::icon icon="heroicon-m-arrow-up-right" />
          </a>
        @empty
          <div class="trace-dashboard__mini-empty">Tidak ada project atau PO yang melewati batas 30 hari.</div>
        @endforelse
      </div>
    </section>

    <div class="trace-dashboard__summary-grid">
      <section class="trace-panel">
        <div class="trace-panel__heading">
          <div>
            <h2>Financial Overview</h2>
            <p>Reconciled totals</p>
          </div>
        </div>
        <div class="trace-financial-total"><small>Total Nilai
            PO</small><strong>{{ $data['financial']['Total Nilai PO'] }}</strong></div>
        <div class="trace-stacked-bar">
          @foreach ($data['financialMix'] as $bar)
            <span class="trace-stacked-bar__{{ $bar['tone'] }}" style="width: {{ $bar['percentage'] }}%"
              title="{{ $bar['label'] }}: {{ $bar['percentage'] }}%"><b>{{ $bar['percentage'] }}%</b></span>
          @endforeach
        </div>
        <div class="trace-stacked-legend">
          @foreach ($data['financialMix'] as $bar)
            <span><i class="trace-stacked-bar__{{ $bar['tone'] }}"></i>{{ $bar['label'] }}
              {{ $bar['percentage'] }}%</span>
          @endforeach
        </div>
        <div class="trace-metrics"><span>Balance <strong>{{ $data['financial']['Balance'] }}</strong></span><span>Sisa
            Budget <strong>{{ $data['financial']['Sisa Budget'] }}</strong></span><span>Pengeluaran
            <strong>{{ $data['financial']['Pengeluaran'] }}</strong></span></div>
      </section>
      <section class="trace-panel">
        <div class="trace-panel__heading">
          <div>
            <h2>Progress & Pengeluaran</h2>
            <p>{{ $data['periodLabel'] }} · {{ $data['progress']['Persentase Invoice'] }} average</p>
          </div>
        </div>
        <div class="trace-progress-list"><span>Invoice / PO progress
            <strong>{{ $data['progress']['Persentase Invoice'] }}</strong></span>
          <div><i
              style="width: {{ min(100, (float) str_replace(',', '.', str_replace('%', '', $data['progress']['Persentase Invoice']))) }}%"></i>
          </div><span>Monthly invoice activity <strong>{{ $data['progress']['Monthly Invoice'] }}</strong></span>
          <div><i class="trace-progress-list__expense"
              style="width: {{ min(100, (float) str_replace(',', '.', str_replace('%', '', $data['progress']['Monthly Invoice']))) }}%"></i>
          </div>
          <span>Material allocation <strong>{{ $data['expenditure']['Material']['amount'] }}
              ({{ $data['expenditure']['Material']['percentage'] }}%)</strong></span>
          <div><i style="width: {{ $data['expenditure']['Material']['percentage'] }}%"></i></div>
          <span>Labour allocation <strong>{{ $data['expenditure']['Labour']['amount'] }}
              ({{ $data['expenditure']['Labour']['percentage'] }}%)</strong></span>
          <div><i class="trace-progress-list__labour"
              style="width: {{ $data['expenditure']['Labour']['percentage'] }}%"></i></div>
        </div>
      </section>
    </div>

    <div class="trace-dashboard__tables">
      <section class="trace-panel trace-table-panel">
        <div class="trace-table-panel__heading">
          <h2>Project Terbaru</h2><a
            href="{{ \App\Filament\Resources\Projects\ProjectResource::getUrl('index') }}">View all →</a>
        </div>
        <div class="trace-table-wrap">
          <table>
            <thead>
              <tr>
                <th>Date</th>
                <th>Project</th>
                <th>Location</th>
                <th>Value</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($data['recentProjects'] as $item)
                <tr>
                  <td>{{ $item['date'] }}</td>
                  <td><a href="{{ $item['url'] }}">{{ $item['title'] }}</a></td>
                  <td><span class="trace-status trace-status--neutral">{{ $item['location'] }}</span></td>
                  <td>—</td>
                </tr>
              @empty
                <tr>
                  <td colspan="4">Belum ada project terbaru.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </section>
      <section class="trace-panel trace-table-panel">
        <div class="trace-table-panel__heading">
          <h2>Purchase Order Terbaru</h2><a
            href="{{ \App\Filament\Resources\PurchaseOrders\PurchaseOrderResource::getUrl('index') }}">View all →</a>
        </div>
        <div class="trace-table-wrap">
          <table>
            <thead>
              <tr>
                <th>Date</th>
                <th>PO Number</th>
                <th>Progress</th>
                <th>Value</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($data['recentOrders'] as $item)
                <tr>
                  <td>{{ $item['date'] }}</td>
                  <td><a href="{{ $item['url'] }}">{{ $item['title'] }}</a></td>
                  <td><span class="trace-status trace-status--success">{{ $item['progress'] }}</span></td>
                  <td>{{ $item['value'] }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="4">Belum ada purchase order terbaru.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </div>
</x-filament-panels::page>

<script>
  if (!window.traceDashboardChartBound) {
    window.traceDashboardChartBound = true;

    const formatChartValue = (value) => new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      maximumFractionDigits: 0,
    }).format(Number(value));

    document.addEventListener('click', (event) => {
      const button = event.target.closest('[data-chart-range]');
      if (!button) return;

      const chart = button.closest('.trace-panel--chart');
      const range = button.dataset.chartRange;
      chart.querySelectorAll('[data-chart-range]').forEach((item) => item.classList.toggle('is-active', item ===
        button));
      chart.querySelectorAll('[data-chart-panel]').forEach((panel) => {
        panel.hidden = panel.dataset.chartPanel !== range;
      });
    });

    document.addEventListener('mousemove', (event) => {
      const panel = event.target.closest('[data-chart-panel]');
      if (!panel || panel.hidden) return;

      const chart = panel.querySelector('svg');
      const points = [...panel.querySelectorAll('[data-chart-point]')];
      if (!chart || !points.length) return;

      const bounds = chart.getBoundingClientRect();
      const viewBoxX = ((event.clientX - bounds.left) / bounds.width) * 1020;
      const closestX = points.reduce((closest, point) => Math.abs(Number(point.getAttribute('cx')) - viewBoxX) <
        Math.abs(Number(closest.getAttribute('cx')) - viewBoxX) ? point : closest);
      const tooltip = panel.closest('.trace-chart').querySelector('[data-chart-tooltip]');
      const pointRect = closestX.getBoundingClientRect();
      const chartRect = panel.closest('.trace-chart').getBoundingClientRect();

      tooltip.innerHTML =
        `<strong>${closestX.dataset.label}</strong><span><i class="trace-tooltip__profit"></i>Profit ${formatChartValue(closestX.dataset.profit)}</span><span><i class="trace-tooltip__expense"></i>Expenditure ${formatChartValue(closestX.dataset.expenditure)}</span><span><i class="trace-tooltip__po"></i>Nilai PO ${formatChartValue(closestX.dataset.po)}</span>`;
      tooltip.style.left =
        `${Math.min(chartRect.width - 180, Math.max(8, pointRect.left - chartRect.left - 85))}px`;
      tooltip.style.top = `${Math.max(8, pointRect.top - chartRect.top - 112)}px`;
      tooltip.hidden = false;
    });

    document.addEventListener('mouseleave', (event) => {
      const chart = event.target.closest('[data-trace-chart]');
      if (chart) chart.querySelector('[data-chart-tooltip]').hidden = true;
    }, true);
  }
</script>
