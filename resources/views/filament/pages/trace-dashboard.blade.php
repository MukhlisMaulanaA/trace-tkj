<x-filament-panels::page>
  @php($data = $this->dashboardData())
  <div class="space-y-6">
    <div
      class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="text-sm font-medium text-primary-600">TRACE TKJ</p>
        <h1 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">Executive monitoring</h1>
        <p class="mt-1 text-sm text-gray-500">Pantau project, PO, progress, dan kondisi finansial.</p>
      </div>
      <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Sumber Project
          <select wire:model.live="source"
            class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:w-40">
            @foreach ($this->sourceOptions() as $value => $label)
              <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
          </select>
        </label>
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Periode
          <select wire:model.live="period"
            class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:w-36">
            @foreach ($this->periodOptions() as $value => $label)
              <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
          </select>
        </label>
        <button type="button" wire:click="refreshDashboard"
          class="inline-flex h-10 items-center justify-center rounded-lg bg-gray-900 px-4 text-sm font-semibold text-white hover:bg-gray-700 dark:bg-white dark:text-gray-900">Refresh</button>
      </div>
    </div>

    @if (!$data['hasData'])
      <div
        class="rounded-xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center dark:border-gray-700 dark:bg-gray-900">
        <p class="font-semibold text-gray-950 dark:text-white">No project data available.</p>
        <p class="mt-1 text-sm text-gray-500">Data akan muncul setelah project atau purchase order tersedia.</p>
      </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      @foreach ($data['kpis'] as $kpi)
        <a href="{{ $kpi['url'] }}"
          class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-primary-300 dark:border-gray-800 dark:bg-gray-900">
          <p class="text-sm text-gray-500">{{ $kpi['label'] }}</p>
          <p class="mt-3 text-2xl font-bold text-gray-950 dark:text-white">{{ $kpi['value'] }}</p>
          <p class="mt-3 text-xs font-semibold text-primary-600">Lihat detail →</p>
        </a>
      @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
      <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-base font-semibold text-gray-950 dark:text-white">Financial overview</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          @foreach ($data['financial'] as $label => $value)
            <div class="border-l-2 border-primary-500 pl-3">
              <p class="text-sm text-gray-500">{{ $label }}</p>
              <p class="mt-1 font-semibold text-gray-950 dark:text-white">{{ $value }}</p>
            </div>
          @endforeach
        </div>
      </section>
      <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-base font-semibold text-gray-950 dark:text-white">Progress & expenditure <span
            class="font-normal text-gray-500">({{ $data['periodLabel'] }})</span></h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          @foreach (array_merge($data['progress'], $data['expenditure']) as $label => $value)
            <div class="border-l-2 border-amber-500 pl-3">
              <p class="text-sm text-gray-500">{{ $label }}</p>
              <p class="mt-1 font-semibold text-gray-950 dark:text-white">{{ $value }}</p>
            </div>
          @endforeach
        </div>
      </section>
    </div>

    <section class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
      <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
        <h2 class="text-base font-semibold text-gray-950 dark:text-white">Attention required</h2>
      </div>
      <div class="divide-y divide-gray-100 dark:divide-gray-800">
        @forelse ($data['attention'] as $item)
          <a href="{{ $item['url'] }}"
            class="flex items-center justify-between gap-4 px-5 py-4 hover:bg-gray-50 dark:hover:bg-gray-800">
            <div><span class="text-xs font-bold text-danger-600">{{ $item['severity'] }}</span>
              <p class="mt-1 font-medium text-gray-950 dark:text-white">{{ $item['title'] }}</p>
              <p class="text-sm text-gray-500">{{ $item['detail'] }}</p>
            </div><span class="text-sm font-semibold text-primary-600">View</span>
        </a>@empty<p class="px-5 py-8 text-sm text-gray-500">Tidak ada kondisi perhatian berdasarkan rule yang
            tersedia.</p>
        @endforelse
      </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-2">
      <section class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h2
          class="border-b border-gray-200 px-5 py-4 text-base font-semibold text-gray-950 dark:border-gray-800 dark:text-white">
          Recent projects</h2>
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
          @forelse ($data['recentProjects'] as $item)
            <a href="{{ $item['url'] }}"
              class="flex items-center justify-between gap-4 px-5 py-4 hover:bg-gray-50 dark:hover:bg-gray-800">
              <div>
                <p class="text-xs text-gray-500">{{ $item['label'] }} · {{ $item['date'] }}</p>
                <p class="mt-1 font-medium text-gray-950 dark:text-white">{{ $item['title'] }}</p>
              </div><span class="text-sm font-semibold text-primary-600">View</span>
          </a>@empty<p class="px-5 py-8 text-sm text-gray-500">No project data available.</p>
          @endforelse
        </div>
      </section>
      <section class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h2
          class="border-b border-gray-200 px-5 py-4 text-base font-semibold text-gray-950 dark:border-gray-800 dark:text-white">
          Recent purchase orders</h2>
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
          @forelse ($data['recentOrders'] as $item)
            <a href="{{ $item['url'] }}"
              class="flex items-center justify-between gap-4 px-5 py-4 hover:bg-gray-50 dark:hover:bg-gray-800">
              <div>
                <p class="text-xs text-gray-500">{{ $item['label'] }} · {{ $item['date'] }}</p>
                <p class="mt-1 font-medium text-gray-950 dark:text-white">{{ $item['title'] }}</p>
              </div><span class="text-sm font-semibold text-primary-600">View</span>
          </a>@empty<p class="px-5 py-8 text-sm text-gray-500">No purchase order data available.</p>
          @endforelse
        </div>
      </section>
    </div>
  </div>
</x-filament-panels::page>
