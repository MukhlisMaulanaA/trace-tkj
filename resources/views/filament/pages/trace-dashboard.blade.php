<x-filament-panels::page>
  @php($data = $this->dashboardData())
  <div class="space-y-6">
    {{-- Header --}}
    <div
      class="flex flex-col gap-5 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="text-sm font-medium text-primary-600 dark:text-primary-400">TRACE TKJ</p>
        <h1 class="mt-0.5 text-2xl font-bold tracking-tight text-gray-950 dark:text-white">Monitoring Project</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pantau project, PO, progress, dan kondisi finansial.
        </p>
      </div>
      <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
          <span>Sumber Project</span>
          <select wire:model.live="source"
            class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm transition focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 sm:w-44">
            @foreach ($this->sourceOptions() as $value => $label)
              <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
          </select>
        </label>
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
          <span>Periode</span>
          <select wire:model.live="period"
            class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm transition focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 sm:w-36">
            @foreach ($this->periodOptions() as $value => $label)
              <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
          </select>
        </label>
        <button type="button" wire:click="refreshDashboard" wire:loading.attr="disabled" wire:target="refreshDashboard"
          class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 text-sm font-semibold text-white transition hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-70 dark:bg-white dark:text-gray-900 dark:focus:ring-white dark:focus:ring-offset-gray-900">
          <span wire:loading.remove wire:target="refreshDashboard">Refresh</span>
          <span wire:loading wire:target="refreshDashboard">Memuat…</span>
        </button>
      </div>
    </div>

    {{-- Empty state --}}
    @if (!$data['hasData'])
      <div
        class="rounded-xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center dark:border-gray-700 dark:bg-gray-900">
        <p class="font-semibold text-gray-950 dark:text-white">No project data available.</p>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Data akan muncul setelah project atau purchase order
          tersedia.</p>
      </div>
    @endif

    {{-- KPI cards --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      @foreach ($data['kpis'] as $kpi)
        <a href="{{ $kpi['url'] }}"
          class="group rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-primary-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-gray-800 dark:bg-gray-900">
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ $kpi['label'] }}</p>
          <p class="mt-3 text-2xl font-bold tabular-nums text-gray-950 dark:text-white">{{ $kpi['value'] }}</p>
          <p
            class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-primary-600 transition group-hover:gap-1.5 dark:text-primary-400">
            Lihat detail <span aria-hidden="true">→</span>
          </p>
        </a>
      @endforeach
    </div>

    {{-- Financial + progress --}}
    <div class="grid gap-6 xl:grid-cols-2">
      <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-base font-semibold text-gray-950 dark:text-white">Financial overview</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          @foreach ($data['financial'] as $label => $value)
            <div class="border-l-2 border-primary-500 pl-3">
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</p>
              <p class="mt-1 font-semibold tabular-nums text-gray-950 dark:text-white">{{ $value }}</p>
            </div>
          @endforeach
        </div>
      </section>
      <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-base font-semibold text-gray-950 dark:text-white">
          Progress & Pengeluaran
          <span class="font-normal text-gray-500 dark:text-gray-400">({{ $data['periodLabel'] }})</span>
        </h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          @foreach (array_merge($data['progress'], $data['expenditure']) as $label => $value)
            <div class="border-l-2 border-amber-500 pl-3">
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</p>
              <p class="mt-1 font-semibold tabular-nums text-gray-950 dark:text-white">{{ $value }}</p>
            </div>
          @endforeach
        </div>  
      </section>
    </div>

    {{-- Attention required --}}
    <section class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
      <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
        <h2 class="text-base font-semibold text-gray-950 dark:text-white">Perlu Perhaitan</h2>
      </div>
      <div class="divide-y divide-gray-100 dark:divide-gray-800">
        @forelse ($data['attention'] as $item)
          <a href="{{ $item['url'] }}"
            class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:hover:bg-gray-800">
            <div class="min-w-0">
              <span
                class="inline-flex rounded-full bg-danger-50 px-2 py-0.5 text-xs font-bold text-danger-600 dark:bg-danger-500/10 dark:text-danger-400">{{ $item['severity'] }}</span>
              <p class="mt-1.5 truncate font-medium text-gray-950 dark:text-white">{{ $item['title'] }}</p>
              <p class="truncate text-sm text-gray-500 dark:text-gray-400">{{ $item['detail'] }}</p>
            </div>
            <span class="shrink-0 text-sm font-semibold text-primary-600 dark:text-primary-400">View</span>
          </a>
        @empty
          <p class="px-5 py-8 text-sm text-gray-500 dark:text-gray-400">Tidak ada kondisi perhatian berdasarkan rule
            yang tersedia.</p>
        @endforelse
      </div>
    </section>

    {{-- Recent projects + orders --}}
    <div class="grid gap-6 xl:grid-cols-2">
      <section class="min-w-0 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h2
          class="border-b border-gray-200 px-5 py-4 text-base font-semibold text-gray-950 dark:border-gray-800 dark:text-white">
          Project Terbaru</h2>
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
          @forelse ($data['recentProjects'] as $item)
            <a href="{{ $item['url'] }}"
              class="flex min-w-0 items-center justify-between gap-4 px-5 py-4 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:hover:bg-gray-800">
              <div class="min-w-0">
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item['label'] }} · {{ $item['date'] }}</p>
                <p class="mt-1 wrap-break-word font-medium text-gray-950 dark:text-white">{{ $item['title'] }}</p>
              </div>
              <span class="shrink-0 text-sm font-semibold text-primary-600 dark:text-primary-400">View</span>
            </a>
          @empty
            <p class="px-5 py-8 text-sm text-gray-500 dark:text-gray-400">No project data available.</p>
          @endforelse
        </div>
      </section>
      <section class="min-w-0 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h2
          class="border-b border-gray-200 px-5 py-4 text-base font-semibold text-gray-950 dark:border-gray-800 dark:text-white">
          Purchase Order Terbaru</h2>
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
          @forelse ($data['recentOrders'] as $item)
            <a href="{{ $item['url'] }}"
              class="flex min-w-0 items-center justify-between gap-4 px-5 py-4 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:hover:bg-gray-800">
              <div class="min-w-0">
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item['label'] }} · {{ $item['date'] }}</p>
                <p class="mt-1 wrap-break-word font-medium text-gray-950 dark:text-white">{{ $item['title'] }}</p>
              </div>
              <span class="shrink-0 text-sm font-semibold text-primary-600 dark:text-primary-400">View</span>
            </a>
          @empty
            <p class="px-5 py-8 text-sm text-gray-500 dark:text-gray-400">No purchase order data available.</p>
          @endforelse
        </div>
      </section>
    </div>
  </div>
</x-filament-panels::page>
