<x-filament-panels::page>
  <?php $cards = $this->reviewCards() ?>

  <div class="-mx-4 min-h-screen bg-[#080b12] px-4 pb-8 text-slate-200 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
    <div class="mx-auto w-full max-w-[1400px] space-y-5 pt-2">
      <header class="grid gap-5 border-b border-slate-800/80 pb-5 xl:grid-cols-[minmax(16rem,0.8fr)_minmax(0,2.2fr)] xl:items-end">
        <div class="min-w-0">
          <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-400">TRACE TKJ</p>
          <div class="mt-1 flex flex-wrap items-baseline gap-x-3 gap-y-1">
            <h1 class="whitespace-nowrap text-xl font-semibold tracking-tight text-slate-100">Status Proyek</h1>
            <span class="text-sm text-slate-500">{{ $cards->count() }} project aktif</span>
          </div>
        </div>
        <div class="grid min-w-0 grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
          <label class="sr-only" for="project-review-search">Cari project</label>
          <input id="project-review-search" type="search" wire:model.live.debounce.300ms="search"
            placeholder="Cari ID, project, atau PO..."
            class="h-10 w-full min-w-0 rounded-md border border-slate-800 bg-[#0d121c] px-3 text-sm text-slate-200 placeholder:text-slate-600 focus:border-sky-500 focus:ring-sky-500">
          <label class="sr-only" for="project-review-source">Sumber project</label>
          <select id="project-review-source" wire:model.live="source"
            class="h-10 rounded-md border border-slate-800 bg-[#0d121c] px-3 text-sm text-slate-300 focus:border-sky-500 focus:ring-sky-500">
            <?php foreach ($this->sourceOptions() as $value => $label): ?>
              <option value="{{ $value }}">{{ $label }}</option>
            <?php endforeach; ?>
          </select>
          <label class="sr-only" for="project-review-completion">Status project</label>
          <select id="project-review-completion" wire:model.live="completion"
            class="h-10 rounded-md border border-slate-800 bg-[#0d121c] px-3 text-sm text-slate-300 focus:border-sky-500 focus:ring-sky-500">
            <?php foreach ($this->completionOptions() as $value => $label): ?>
              <option value="{{ $value }}">{{ $label }}</option>
            <?php endforeach; ?>
          </select>
          <label class="sr-only" for="project-review-sort">Urutkan project</label>
          <select id="project-review-sort" wire:model.live="sort"
            class="h-10 rounded-md border border-slate-800 bg-[#0d121c] px-3 text-sm text-slate-300 focus:border-sky-500 focus:ring-sky-500">
            <?php foreach ($this->sortOptions() as $value => $label): ?>
              <option value="{{ $value }}">{{ $label }}</option>
            <?php endforeach; ?>
          </select>
          <label class="sr-only" for="project-review-direction">Arah pengurutan</label>
          <select id="project-review-direction" wire:model.live="direction"
            class="h-10 rounded-md border border-slate-800 bg-[#0d121c] px-3 text-sm text-slate-300 focus:border-sky-500 focus:ring-sky-500">
            <?php foreach ($this->directionOptions() as $value => $label): ?>
              <option value="{{ $value }}">{{ $label }}</option>
            <?php endforeach; ?>
          </select>
        </div>
      </header>

      <?php if ($cards->isEmpty()): ?>
        <section class="rounded-lg border border-dashed border-slate-800 bg-[#0d121c] px-6 py-16 text-center">
          <p class="font-semibold text-slate-200">Tidak ada project yang sesuai.</p>
          <p class="mt-1 text-sm text-slate-500">Coba ubah pencarian atau filter sumber project.</p>
        </section>
      <?php else: ?>
        <div class="grid gap-3 lg:grid-cols-2">
          <?php foreach ($cards as $card): ?>
            @php
              $workTone = $card['work_status']['tone'];
              $workBar = match ($workTone) {
                  'success' => 'bg-emerald-500',
                  'warning' => 'bg-amber-500',
                  default => 'bg-blue-500',
              };
              $workBadge = match ($workTone) {
                  'success' => 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/20',
                  'warning' => 'bg-amber-500/10 text-amber-400 ring-amber-500/20',
                  default => 'bg-blue-500/10 text-blue-400 ring-blue-500/20',
              };
              $workText = match ($workTone) {
                  'success' => 'text-emerald-400',
                  'warning' => 'text-amber-400',
                  default => 'text-blue-400',
              };
                $statusBadge = $card['is_completed']
                  ? 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/20'
                  : $workBadge;
                $statusLabel = $card['is_completed'] ? 'Project Completed' : $card['work_status']['label'];
            @endphp
            <article
              class="flex min-w-0 flex-col overflow-hidden rounded-lg border border-slate-800 bg-[#0d121c] shadow-[0_12px_30px_rgba(0,0,0,0.16)]">
              <div class="border-b border-slate-800/80 px-4 py-4">
                <div class="flex items-start justify-between gap-3">
                  <div class="min-w-0">
                    <div
                      class="flex items-center gap-2 text-xs font-medium uppercase tracking-[0.14em] text-slate-500">
                      <span>{{ $card['project_id'] }}</span><span
                        class="text-slate-700">•</span><span>{{ $card['source'] }}</span>
                    </div>
                    <a href="{{ $this->projectUrl($card['project']) }}"
                      class="mt-1 block truncate text-base font-semibold text-slate-100 hover:text-sky-400">{{ $card['project_name'] }}</a>
                    <p class="mt-1 truncate text-xs text-slate-500">{{ $card['customer'] }} ·
                      {{ $card['location'] }}</p>
                  </div>
                  <span
                    class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-[0.1em] ring-1 {{ $statusBadge }}">{{ $statusLabel }}</span>
                </div>
              </div>

              <div class="space-y-4 px-4 py-4">
                <div>
                  <div
                    class="mb-1.5 flex flex-wrap items-center justify-between gap-x-3 gap-y-1 text-xs uppercase tracking-[0.12em] text-slate-500">
                    <span>Progress pekerjaan</span><span
                      class="font-semibold {{ $workText }}">{{ $this->percentage($card['work_progress']) }}</span>
                  </div>
                  <div class="h-2 overflow-hidden rounded-full bg-slate-800">
                    <div class="h-full rounded-full {{ $workBar }}"
                      style="width: {{ min(100, max(0, $card['work_progress'])) }}%"></div>
                  </div>
                  <p class="mt-1.5 truncate text-xs text-slate-500" title="{{ $card['work_note'] }}">
                    {{ $card['work_note'] }}</p>
                </div>
                <div>
                  <div
                    class="mb-1.5 flex flex-wrap items-center justify-between gap-x-3 gap-y-1 text-xs uppercase tracking-[0.12em] text-slate-500">
                    <span>Progress tagihan</span><span
                      class="font-semibold text-sky-400">{{ $this->percentage($card['payment_progress']) }}</span>
                  </div>
                  <div class="h-2 overflow-hidden rounded-full bg-slate-800">
                    <div class="h-full rounded-full bg-sky-500"
                      style="width: {{ min(100, max(0, $card['payment_progress'])) }}%"></div>
                  </div>
                  <div class="mt-1.5 flex flex-wrap justify-between gap-x-3 gap-y-1 text-xs text-slate-500">
                    <span>{{ $this->money($card['payment_value']) }} dibayar</span><span>Sisa
                      {{ $this->money($card['payment_remaining']) }}</span></div>
                </div>
              </div>

              <div class="border-t border-slate-800/80 px-4 py-3">
                <div
                  class="mb-2 flex items-center justify-between text-xs uppercase tracking-[0.12em] text-slate-500">
                  <span>Purchase orders</span><span>{{ $card['po_count'] }} PO · {{ $card['invoice_count'] }}
                    invoice</span></div>
                <div class="space-y-1.5">
                  <?php if ($card['purchase_orders']->isEmpty()): ?>
                    <p class="py-2 text-xs text-slate-500">Belum ada purchase order.</p>
                  <?php else: ?>
                    <?php foreach ($card['purchase_orders'] as $purchaseOrder): ?>
                      @php
                        $poTone = $purchaseOrder['status']['tone'];
                        $poBar = match ($poTone) {
                            'success' => 'bg-emerald-500',
                            'warning' => 'bg-amber-500',
                            default => 'bg-blue-500',
                        };
                        $poText = match ($poTone) {
                            'success' => 'text-emerald-400',
                            'warning' => 'text-amber-400',
                            default => 'text-blue-400',
                        };
                      @endphp
                      <details class="group rounded-md border border-slate-800 bg-[#111824] open:bg-[#141c29]">
                        <summary
                          class="flex min-w-0 cursor-pointer list-none items-center gap-2 px-2 py-2 [&::-webkit-details-marker]:hidden">
                          <a href="{{ $purchaseOrder['url'] }}"
                            class="w-20 shrink-0 text-xs font-medium text-slate-300 hover:text-sky-400 sm:w-24"
                            onclick="event.stopPropagation()">{{ $purchaseOrder['po_number'] }}</a>
                          <div class="min-w-0 flex-1">
                            <div class="h-1 overflow-hidden rounded-full bg-slate-800">
                              <div class="h-full rounded-full {{ $poBar }}"
                                style="width: {{ min(100, max(0, $purchaseOrder['payment_progress'])) }}%"></div>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">{{ $purchaseOrder['invoice_count'] }} invoice ·
                              {{ $this->money($purchaseOrder['invoice_value']) }}</p>
                          </div>
                          <span
                            class="shrink-0 text-xs font-semibold {{ $poText }}">{{ $this->percentage($purchaseOrder['payment_progress']) }}</span><span
                            class="text-sm text-slate-500 transition group-open:rotate-180">⌄</span>
                        </summary>
                        <div class="border-t border-slate-800 px-2 pb-2 pt-1.5">
                          <?php if ($purchaseOrder['invoices']->isEmpty()): ?>
                            <p class="py-1 text-xs text-slate-500">Belum ada invoice.</p>
                          <?php else: ?>
                            <?php foreach ($purchaseOrder['invoices']->take(3) as $invoice): ?>
                              <div class="flex items-center gap-2 py-1 text-[9px] text-slate-500"><span
                                  class="h-1.5 w-1.5 shrink-0 rounded-full {{ $poBar }}"></span><span
                                  class="min-w-0 flex-1 truncate text-xs">{{ $invoice->title ?: $invoice->description ?: 'Invoice' }}</span><span
                                  class="shrink-0 text-xs">{{ $invoice->invoice_date?->format('d M Y') ?? '-' }}</span><span
                                  class="shrink-0 text-xs font-medium text-slate-300">{{ $this->money($invoice->amount) }}</span>
                              </div>
                            <?php endforeach; ?>
                          <?php endif; ?>
                        </div>
                      </details>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
              </div>

              <div
                class="mt-auto flex flex-wrap items-center justify-between gap-x-3 gap-y-1 border-t border-slate-800/80 bg-[#0a0f17] px-4 py-3 text-xs text-slate-500">
                <span>Aktivitas terakhir
                  {{ $card['latest_activity_date']?->format('d M Y') ?? '-' }}</span><span>Value: {{ $this->money($card['po_value']) }}</span>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</x-filament-panels::page>
