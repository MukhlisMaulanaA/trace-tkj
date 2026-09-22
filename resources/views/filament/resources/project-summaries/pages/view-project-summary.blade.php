@php
  /** @var \App\Models\ProjectSummary $record */
  $project = $record->project;
  $materialExpenditures = $record->expenditures()
      ->where('type', \App\Models\ProjectExpenditure::TYPE_MATERIAL)
      ->orderBy('expenditure_date')
      ->get();
  $labourExpenditures = $record->expenditures()
      ->where('type', \App\Models\ProjectExpenditure::TYPE_LABOUR)
      ->orderBy('expenditure_date')
      ->get();
  $formatCurrency = static fn (float|int|null $amount): string => 'Rp' . number_format((float) ($amount ?? 0), 0, '.', ',');
@endphp

<x-filament-panels::page>
  <div class="space-y-6">
    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
      <div class="border-b border-gray-200 bg-gray-50 px-5 py-4 dark:border-white/10 dark:bg-white/3 sm:px-8">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-primary-600 dark:text-primary-400">Project Summary</p>
            <h2 class="mt-1 text-xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-2xl">{{ $record->project_name }}</h2>
          </div>
          <p class="text-sm text-gray-500 dark:text-gray-400">Owner: <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $record->owner_customer }}</span></p>
        </div>
      </div>

      <div class="grid gap-px bg-gray-200 sm:grid-cols-2 lg:grid-cols-4 dark:bg-white/10">
        <div class="bg-white px-5 py-4 dark:bg-gray-900">
          <p class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Project Name</p>
          <p class="mt-1 font-semibold text-gray-950 dark:text-white">{{ $record->project_name }}</p>
        </div>
        <div class="bg-white px-5 py-4 dark:bg-gray-900">
          <p class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">PO Number</p>
          <p class="mt-1 font-semibold text-gray-950 dark:text-white">{{ $record->po_numbers ?: '-' }}</p>
        </div>
        <div class="bg-white px-5 py-4 dark:bg-gray-900">
          <p class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Owner / Customer</p>
          <p class="mt-1 font-semibold text-gray-950 dark:text-white">{{ $record->owner_customer }}</p>
        </div>
        <div class="bg-white px-5 py-4 dark:bg-gray-900">
          <p class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">PPH 2.65%</p>
          <p class="mt-1 font-semibold text-gray-950 dark:text-white">{{ $formatCurrency($record->pph_amount) }}</p>
        </div>
      </div>

      <div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-4 sm:p-8">
        <div class="rounded-lg border border-gray-200 p-4 dark:border-white/10">
          <p class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Contract Value</p>
          <p class="mt-2 text-lg font-bold text-gray-950 dark:text-white">{{ $formatCurrency($record->contract_value) }}</p>
        </div>
        <div class="rounded-lg border border-primary-200 bg-primary-50 p-4 dark:border-primary-500/30 dark:bg-primary-500/10">
          <p class="text-xs font-semibold uppercase text-primary-700 dark:text-primary-300">Final Contract Value</p>
          <p class="mt-2 text-lg font-bold text-primary-900 dark:text-primary-100">{{ $formatCurrency($record->final_contract_value) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 p-4 dark:border-white/10">
          <p class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">M + L Expenditure</p>
          <p class="mt-2 text-lg font-bold text-gray-950 dark:text-white">{{ $formatCurrency($record->ml_expenditure) }}</p>
        </div>
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-500/30 dark:bg-emerald-500/10">
          <p class="text-xs font-semibold uppercase text-emerald-700 dark:text-emerald-300">Remaining Budget</p>
          <p class="mt-2 text-lg font-bold text-emerald-900 dark:text-emerald-100">{{ $formatCurrency($isEditingProfit ? $this->draftRemainingBudget : $record->remaining_budget) }}</p>
        </div>
      </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_18rem]">
      <div class="space-y-6">
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
          <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-white/10 sm:px-6">
            <div>
              <h3 class="font-bold text-gray-950 dark:text-white">Material Expenditure</h3>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $materialExpenditures->count() }} entries</p>
            </div>
            <span class="font-bold text-gray-950 dark:text-white">{{ $formatCurrency($record->material_expenditure) }}</span>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full min-w-150 text-left text-sm">
              <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-white/3 dark:text-gray-400">
                <tr><th class="px-5 py-3 font-semibold">No.</th><th class="px-5 py-3 font-semibold">Material</th><th class="px-5 py-3 font-semibold">Value</th><th class="px-5 py-3 font-semibold">Purchase Date</th></tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                @forelse ($materialExpenditures as $expenditure)
                  <tr><td class="px-5 py-3 text-gray-500">{{ $loop->iteration }}</td><td class="px-5 py-3 font-medium text-gray-950 dark:text-white">{{ $expenditure->description }}</td><td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ $formatCurrency($expenditure->amount) }}</td><td class="px-5 py-3 text-gray-500 dark:text-gray-400">{{ $expenditure->expenditure_date?->format('d/m/Y') }}</td></tr>
                @empty
                  <tr><td colspan="4" class="px-5 py-8 text-center text-gray-500">No material expenditure recorded.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
          <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-white/10 sm:px-6">
            <div>
              <h3 class="font-bold text-gray-950 dark:text-white">Labour Expenditure</h3>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $labourExpenditures->count() }} entries</p>
            </div>
            <span class="font-bold text-gray-950 dark:text-white">{{ $formatCurrency($record->labour_expenditure) }}</span>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full min-w-150 text-left text-sm">
              <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-white/3 dark:text-gray-400">
                <tr><th class="px-5 py-3 font-semibold">No.</th><th class="px-5 py-3 font-semibold">Labour</th><th class="px-5 py-3 font-semibold">Value</th><th class="px-5 py-3 font-semibold">Period</th></tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                @forelse ($labourExpenditures as $expenditure)
                  <tr><td class="px-5 py-3 text-gray-500">{{ $loop->iteration }}</td><td class="px-5 py-3 font-medium text-gray-950 dark:text-white">{{ $expenditure->description }}</td><td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ $formatCurrency($expenditure->amount) }}</td><td class="px-5 py-3 text-gray-500 dark:text-gray-400">{{ $expenditure->expenditure_date?->format('d/m/Y') }}</td></tr>
                @empty
                  <tr><td colspan="4" class="px-5 py-8 text-center text-gray-500">No labour expenditure recorded.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <aside class="space-y-6">
        <div class="rounded-xl border border-orange-200 bg-orange-50 p-5 shadow-sm dark:border-orange-500/30 dark:bg-orange-500/10">
          @if (!$isEditingProfit)
            <p class="text-xs font-semibold uppercase tracking-wide text-orange-700 dark:text-orange-300">Profit</p>
            <p class="mt-3 text-3xl font-bold text-orange-950 dark:text-orange-100">{{ number_format((float) $record->profit_percentage, 2) }}%</p>
            <div class="mt-4 border-t border-orange-200 pt-4 dark:border-orange-500/20">
              <p class="text-xs font-semibold uppercase text-orange-700 dark:text-orange-300">Final Profit Amount</p>
              <p class="mt-1 text-xl font-bold text-orange-950 dark:text-orange-100">{{ $formatCurrency($record->final_profit) }}</p>
            </div>
            <p class="mt-3 text-xs text-orange-800/80 dark:text-orange-200/80">Mode: {{ ucfirst($record->profit_mode) }}</p>
            <button type="button" wire:click="startProfitEditing" class="mt-5 inline-flex items-center rounded-lg bg-orange-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 dark:focus:ring-offset-orange-950">
              Edit
            </button>
          @else
            <p class="text-xs font-semibold uppercase tracking-wide text-orange-700 dark:text-orange-300">Edit Profit</p>
            <div class="mt-4 space-y-4">
              <label class="block">
                <span class="text-sm font-semibold text-orange-900 dark:text-orange-100">Mode</span>
                <select wire:model.live="draftProfitMode" class="mt-1 block w-full rounded-lg border-orange-300 bg-white text-sm shadow-sm focus:border-orange-500 focus:ring-orange-500 dark:border-orange-500/40 dark:bg-gray-950 dark:text-white">
                  <option value="percentage">Percentage</option>
                  <option value="nominal">Nominal</option>
                </select>
              </label>
              <label class="block">
                <span class="text-sm font-semibold text-orange-900 dark:text-orange-100">Profit Percentage</span>
                <div class="mt-1 flex rounded-lg shadow-sm">
                  <input type="number" step="0.01" min="0" max="100" wire:model.live="draftProfitPercentage" class="block w-full min-w-0 rounded-l-lg border-orange-300 bg-white text-sm focus:border-orange-500 focus:ring-orange-500 dark:border-orange-500/40 dark:bg-gray-950 dark:text-white">
                  <span class="inline-flex items-center rounded-r-lg border border-l-0 border-orange-300 bg-orange-100 px-3 text-sm font-semibold text-orange-800 dark:border-orange-500/40 dark:bg-orange-500/20 dark:text-orange-100">%</span>
                </div>
              </label>
              <label class="block">
                <span class="text-sm font-semibold text-orange-900 dark:text-orange-100">Nominal Profit</span>
                <div
                  x-data="{
                    formatNominalProfit(event) {
                      const rawValue = event.target.value.replace(/[^0-9]/g, '');
                      const formattedValue = rawValue ? Number(rawValue).toLocaleString('en-US') : '';

                      event.target.value = formattedValue;
                      $wire.set('draftNominalProfit', rawValue || null);
                    },
                  }"
                  class="mt-1 flex rounded-lg shadow-sm">
                  <span class="inline-flex items-center rounded-l-lg border border-r-0 border-orange-300 bg-orange-100 px-3 text-sm font-semibold text-orange-800 dark:border-orange-500/40 dark:bg-orange-500/20 dark:text-orange-100">Rp</span>
                  <input
                    type="text"
                    inputmode="numeric"
                    autocomplete="off"
                    value="{{ number_format((float) ($draftNominalProfit ?? 0), 0, '.', ',') }}"
                    x-on:input="formatNominalProfit($event)"
                    class="block w-full min-w-0 rounded-r-lg border-orange-300 bg-white text-right text-sm focus:border-orange-500 focus:ring-orange-500 dark:border-orange-500/40 dark:bg-gray-950 dark:text-white">
                </div>
              </label>
            </div>
            <div class="mt-5 border-t border-orange-200 pt-4 dark:border-orange-500/20">
              <p class="text-xs font-semibold uppercase text-orange-700 dark:text-orange-300">Live Preview</p>
              <p class="mt-2 text-3xl font-bold text-orange-950 dark:text-orange-100">{{ number_format((float) $draftProfitPercentage, 2) }}%</p>
              <p class="mt-3 text-xs font-semibold uppercase text-orange-700 dark:text-orange-300">Final Profit Amount</p>
              <p class="mt-1 text-xl font-bold text-orange-950 dark:text-orange-100">{{ $formatCurrency($this->draftFinalProfit) }}</p>
            </div>
            <div class="mt-5 flex flex-wrap gap-2">
              <button type="button" wire:click="saveProfitChanges" wire:loading.attr="disabled" class="inline-flex items-center rounded-lg bg-orange-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-700 disabled:cursor-wait disabled:opacity-60">
                <span wire:loading.remove wire:target="saveProfitChanges">Save Changes</span>
                <span wire:loading wire:target="saveProfitChanges">Saving...</span>
              </button>
              <button type="button" wire:click="cancelProfitChanges" wire:loading.attr="disabled" class="inline-flex items-center rounded-lg border border-orange-300 bg-transparent px-3 py-2 text-sm font-semibold text-orange-800 transition hover:bg-orange-100 dark:border-orange-500/40 dark:text-orange-100 dark:hover:bg-orange-500/20">
                Cancel
              </button>
            </div>
            @error('profit_mode') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            @error('profit_percentage') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            @error('nominal_profit') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
          @endif
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
          <p class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Balance</p>
          <p class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">{{ $formatCurrency($isEditingProfit ? $this->draftBalance : $record->balance) }}</p>
          <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Final profit + remaining budget + M + L expenditure.</p>
        </div>
      </aside>
    </section>

    <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-600 dark:border-white/15 dark:bg-white/3 dark:text-gray-300">
      Use the expenditure table below to add or edit material and labour costs directly from this summary.
    </div>
  </div>

  {{ $this->content }}
</x-filament-panels::page>