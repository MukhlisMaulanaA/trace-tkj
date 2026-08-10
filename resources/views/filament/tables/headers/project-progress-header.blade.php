@php
  $latestProgress = $project
      ->progresses()
      ->where('is_system', false)
      ->orderByDesc('waktu_progres')
      ->orderByDesc('id')
      ->first();

  $currentProgress = max(0, min(100, (int) ($latestProgress?->persentase ?? 0)));

  $status = match (true) {
      $currentProgress >= 100 => 'Selesai',
      $currentProgress >= 75 => 'Hampir selesai',
      $currentProgress >= 50 => 'Berjalan baik',
      $currentProgress >= 25 => 'Sedang berjalan',
      $currentProgress > 0 => 'Baru dimulai',
      default => 'Belum dimulai',
  };
@endphp

<div
  class="flex items-center justify-between border-b border-gray-200
    bg-white px-6 py-4
    dark:border-white/10 dark:bg-gray-900">

  <div>
    <h3 class="text-base font-semibold text-gray-950 dark:text-white">
      Timeline Progres
    </h3>

    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
      Riwayat perkembangan project
    </p>
  </div>

  <x-filament::button size="sm" icon="heroicon-m-plus" wire:click="mountTableAction('create')">
    Tambah Progress
  </x-filament::button>

</div>

<div class="border-b border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900">

  <div class="px-6 py-5">

    {{-- Header --}}
    <div class="flex flex-wrap items-end justify-between gap-4">

      <div>
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">
          Progress saat ini
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
          {{ $latestProgress ? 'Update terakhir' : 'Status awal project' }}
        </p>

        <p class="mt-0.5 text-sm font-semibold text-gray-700 dark:text-gray-300">
          {{ $latestProgress ? $latestProgress->waktu_progres?->translatedFormat('j F Y, H:i') ?? '-' : 'Project Created' }}
        </p>

      </div>

    </div>


    {{-- Progress Bar --}}
    <div class="mt-6">

      {{-- Bar --}}
      <div class="relative">

        <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">

          <div class="h-full rounded-full bg-primary-600 transition-all duration-500 ease-out"
            style="width: {{ $currentProgress }}%;"></div>

        </div>


        {{-- Current progress marker --}}
        <div class="absolute top-1/2 -translate-y-1/2"
          style="
                        left: {{ $currentProgress }}%;
                        transform:
                            translate(
                                {{ $currentProgress === 100 ? '-100%' : ($currentProgress === 0 ? '0%' : '-50%') }},
                                -50%
                            );
                    ">
          <div
            class="h-4 w-4 rounded-full border-2 border-white
                        bg-primary-600 shadow-md
                        dark:border-gray-900">
          </div>
        </div>

      </div>


      {{-- Scale + Dynamic percentage --}}
      <div class="relative mt-3 h-6 text-xs">

        {{-- 0% --}}
        <span class="absolute left-0 font-medium text-gray-400 dark:text-gray-500">
          0%
        </span>


        {{-- Dynamic percentage --}}
        <span
          class="absolute whitespace-nowrap
                        rounded-md bg-primary-50 px-2 py-0.5
                        text-xs font-bold text-primary-700
                        ring-1 ring-inset ring-primary-600/20
                        dark:bg-primary-500/10
                        dark:text-primary-300
                        dark:ring-primary-400/30"
          style="
                        left: {{ $currentProgress }}%;
                        transform:
                            translateX(
                                {{ $currentProgress === 100 ? '-100%' : ($currentProgress === 0 ? '0%' : '-50%') }}
                            );
                    ">
          {{ $currentProgress }}%
        </span>


        {{-- 100% --}}
        <span class="absolute right-0 font-medium text-gray-400 dark:text-gray-500">
          100%
        </span>

      </div>

    </div>

  </div>

</div>
