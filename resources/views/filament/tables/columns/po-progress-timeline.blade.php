@php
  /** @var \App\Models\PurchaseOrderProgress $record */

  $isSystem = (bool) $record->is_system;
  $progress = (int) ($record->percentage ?? 0);
  $dateText = $record->invoice_date?->translatedFormat('j F Y, H:i') ?? '-';
  $title = $record->title ?? 'Invoice';
  $amount = (float) $record->amount;
  $formattedAmount = 'Rp ' . number_format((int) $amount, 0, ',', '.');
@endphp

<div class="relative">

  <div class="flex gap-4">

    {{-- ================================================= --}}
    {{-- TIMELINE NODE                                     --}}
    {{-- ================================================= --}}
    <div class="flex w-5 shrink-0 flex-col items-center">

      <span @class([
          'mt-1.5 h-3 w-3 shrink-0 rounded-full ring-4',
          'bg-gray-400 ring-gray-400/15 dark:bg-gray-500' => $isSystem,
          'bg-primary-600 ring-primary-600/15' => !$isSystem,
      ])></span>

      <span class="mt-1 w-px flex-1 bg-gray-200 dark:bg-white/10"></span>

    </div>


    {{-- ================================================= --}}
    {{-- CONTENT                                           --}}
    {{-- ================================================= --}}
    <div class="min-w-0 flex-1 pb-7">

      {{-- Top row --}}
      <div class="flex flex-wrap items-start justify-between gap-x-4 gap-y-2">

        <div class="min-w-0">

          <div class="flex flex-wrap items-center gap-2">

            <h4 class="text-sm font-bold leading-5 text-gray-950 dark:text-white">
              {{ $title }}
            </h4>

            @if ($isSystem)
              <span
                class="inline-flex items-center rounded-full
                                bg-gray-100 px-2 py-0.5
                                text-[11px] font-semibold
                                text-gray-600
                                ring-1 ring-inset ring-gray-500/20
                                dark:bg-white/5 dark:text-gray-300
                                dark:ring-white/10">
                System Event
              </span>
            @else
              <span
                class="inline-flex items-center rounded-full
                                bg-primary-50 px-2 py-0.5
                                text-xs font-semibold
                                text-primary-700
                                ring-1 ring-inset ring-primary-600/20
                                dark:bg-primary-500/10
                                dark:text-primary-300
                                dark:ring-primary-400/30">
                {{ $progress }}%
              </span>
            @endif

          </div>

          {{-- Date & Amount --}}
          <p class="mt-1 text-sm font-medium text-gray-400 dark:text-gray-500">
            {{ $dateText }} · {{ $formattedAmount }}
          </p>

        </div>

      </div>

      {{-- PDF indicator --}}
      @if (filled($record->pdf_file))
        <div class="mt-3">
          <a
            href="{{ Storage::disk('public')->url($record->pdf_file) }}"
            target="_blank"
            class="inline-flex items-center gap-2 rounded-lg bg-gray-50 px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100 dark:bg-white/[0.03] dark:text-gray-400 dark:hover:bg-white/[0.06]">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
              <path
                fill-rule="evenodd"
                d="M8 4a2 2 0 012-2h5.293a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V15a3 3 0 01-3 3H5a3 3 0 01-3-3V7a3 3 0 013-3h3zm5 1a1 1 0 100-2 1 1 0 000 2z"
                clip-rule="evenodd" />
            </svg>
            {{ basename($record->pdf_file) }}
          </a>
        </div>
      @endif

    </div>

  </div>

</div>
