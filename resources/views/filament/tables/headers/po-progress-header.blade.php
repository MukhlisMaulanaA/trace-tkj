@php
  $purchaseOrder = $purchaseOrder;

  $lastProgress = $purchaseOrder
      ->progresses()
      ->where('is_system', false)
      ->orderByDesc('invoice_date')
      ->orderByDesc('id')
      ->first();

  $totalPaid = (float) $purchaseOrder->progresses()->sum('amount');

  $grandTotal = (float) ($purchaseOrder->grand_total ?? 0);

  $currentProgress = $grandTotal > 0 ? max(0, min(1000, round(($totalPaid / $grandTotal) * 100, 2))) : 0;

  $status = match (true) {
      $currentProgress >= 100 => 'Selesai dibayar',
      $currentProgress >= 75 => 'Hampir selesai',
      $currentProgress >= 50 => 'Pembayaran berjalan',
      $currentProgress >= 25 => 'Sedang dibayar',
      $currentProgress > 0 => 'Pembayaran dimulai',
      default => 'Belum dibayar',
  };
@endphp

<div
  class="flex items-center justify-between border-b border-gray-200
    bg-white px-6 py-4
    dark:border-white/10 dark:bg-gray-900">

  <div>
    <h3 class="text-base font-semibold text-gray-950 dark:text-white">
      Invoice Progress Timeline
    </h3>

    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
      Riwayat pembayaran PO
    </p>
  </div>

  <x-filament::button size="sm" icon="heroicon-m-plus" wire:click="mountTableAction('create')">
    Tambah Invoice
  </x-filament::button>

</div>

<div class="border-b border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900">

  <div class="px-6 py-5">

    {{-- Header --}}
    <div class="flex flex-wrap items-end justify-between gap-4">

      <div>
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">
          Total Pembayaran
        </p>

        <div class="mt-1 flex items-center gap-3">

          <span class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
            {{ $currentProgress }}%
          </span>

          <span @class([
              'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset',
          
              'bg-gray-100 text-gray-600 ring-gray-500/20
                                                                               dark:bg-white/5 dark:text-gray-300 dark:ring-white/10' =>
                  $currentProgress === 0,
          
              'bg-primary-50 text-primary-700 ring-primary-600/20
                                                                               dark:bg-primary-500/10 dark:text-primary-300 dark:ring-primary-400/30' =>
                  $currentProgress > 0 && $currentProgress < 100,
          
              'bg-success-50 text-success-700 ring-success-600/20
                                                                               dark:bg-success-500/10 dark:text-success-300 dark:ring-success-400/30' =>
                  $currentProgress >= 100,
          ])>
            {{ $status }}
          </span>

        </div>
      </div>

      <div class="text-right">

        <p class="text-xs font-medium text-gray-400 dark:text-gray-500">
          {{ $lastProgress ? 'Pembayaran terakhir' : 'Belum ada pembayaran' }}
        </p>

        <p class="mt-0.5 text-sm font-semibold text-gray-700 dark:text-gray-300">
          {{ $lastProgress ? $lastProgress->invoice_date?->translatedFormat('j F Y, H:i') ?? '-' : '-' }}
        </p>

      </div>

    </div>


    {{-- Progress Bar --}}
    <div class="mt-6">

      {{-- Bar --}}
      <div class="relative">

        {{-- Track --}}
        <div class="relative h-2.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-white/10">

          {{-- Filled Progress --}}
          <div class="h-full rounded-full bg-primary-600 transition-all duration-500 ease-out"
            style="width: {{ $currentProgress }}%;"></div>

        </div>

        {{-- Percentage Marker --}}
        @if ($currentProgress > 0 && $currentProgress < 100)
          <div class="absolute top-0 -translate-x-1/2 -translate-y-1/2 transition-[left] duration-700 ease-out"
            style="left: {{ number_format($currentProgress, 2, '.', '') }}%;">
            <div
              class="h-4 w-4 rounded-full border-2 border-white bg-primary-600 shadow-sm dark:border-gray-900 dark:bg-primary-500">
            </div>
          </div>
        @endif

      </div>

      {{-- Labels --}}
      <div class="mt-3 flex justify-between text-xs font-medium text-gray-600 dark:text-gray-400">

        <span>
          Rp {{ number_format((int) $totalPaid, 0, ',', '.') }}
        </span>

        <span>
          Rp {{ number_format((int) $grandTotal, 0, ',', '.') }}
        </span>

      </div>

    </div>

  </div>

</div>
