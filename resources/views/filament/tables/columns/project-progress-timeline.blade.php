@php
  /** @var \App\Models\ProjectProgress $record */

  $isSystem = (bool) $record->is_system;

  $progress = (int) ($record->persentase ?? 0);

  $dateText = $record->waktu_progres?->translatedFormat('j F Y, H:i') ?? '-';

  $title = $isSystem
      ? 'Project Created'
      : $record->keterangan_singkat ?? \Illuminate\Support\Str::limit($record->keterangan, 70);
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


          {{-- Date --}}
          <p class="mt-1 text-sm font-medium text-gray-400 dark:text-gray-500">
            {{ $dateText }}
          </p>

        </div>

      </div>


      {{-- Description --}}
      @if (!$isSystem && filled($record->keterangan))
        <div
          class="mt-3 rounded-lg
                    bg-gray-50 px-3.5 py-3
                    ring-inset ring-gray-950/5
                    dark:bg-white/[0.03]
                    dark:ring-white/5">

          <p class=" text-sm leading-6 text-gray-600 dark:text-gray-300">
            {{ $record->keterangan }}
          </p>

        </div>
      @endif

    </div>

  </div>

</div>
