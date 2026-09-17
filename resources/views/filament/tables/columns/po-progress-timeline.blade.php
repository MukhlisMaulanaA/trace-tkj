@php
  /** @var \App\Models\PurchaseOrderProgress $record */

  $isSystem = (bool) $record->is_system;
  $progress = round(max(0, min(100, (float) ($record->percentage ?? 0))), 2);
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
          @if (filled($record->description))
            <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-gray-600 dark:text-gray-300">
              {{ $record->description }}
            </p>
          @endif

        </div>

      </div>

      {{-- PDF indicator --}}
      @if (filled($record->pdf_file))
        <div class="mt-3">
          <a href="{{ Storage::disk('public')->url($record->pdf_file) }}" target="_blank" rel="noopener noreferrer"
            x-on:click.stop
            class="inline-flex items-center gap-2 rounded-lg bg-gray-50 px-3 py-2 text-xs font-medium text-gray-600 transition hover:bg-gray-100 dark:bg-white/[0.03] dark:text-gray-400 dark:hover:bg-white/[0.06]">
            {{-- PDF Icon --}}
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M7 3.5h6.5L19 9v11.5A1.5 1.5 0 0117.5 22h-10A1.5 1.5 0 016 20.5v-15A2 2 0 018 3.5z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 3.5V9h5.5" />
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 15h1.5a1.5 1.5 0 000-3H9v5m6-5h-1v5h1a2.5 2.5 0 000-5z" />
            </svg>

            <span>
              Lihat Invoice
            </span>

            {{-- New Tab Icon --}}
            <svg class="h-3.5 w-3.5 opacity-60" fill="none" stroke="currentColor" stroke-width="1.8"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M14 5h5v5" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 5l-8 8" />
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M19 13v5.5A1.5 1.5 0 0117.5 20h-11A1.5 1.5 0 015 18.5v-11A1.5 1.5 0 016.5 6H12" />
            </svg>
          </a>
        </div>
      @endif

    </div>

  </div>

</div>
