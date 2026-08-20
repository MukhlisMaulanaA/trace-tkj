@php
  /** @var \App\Models\PurchaseOrder $record */

  $subtotal = (float) ($record->subtotal ?? 0);
  $ppnAmount = (float) ($record->ppn_amount ?? 0);

  $totalAfterPpn = $subtotal + $ppnAmount;

  $discountEnabled = (bool) ($record->discount_enabled ?? false);
  $discountPercent = (float) ($record->discount_percent ?? 0);
  $discountAmount = (float) ($record->discount_amount ?? 0);

  $grandTotal = (float) ($record->grand_total ?? $totalAfterPpn);
@endphp

<td colspan="100%" class="p-0">
  <div class="border-t border-gray-200 bg-gray-50 px-6 py-5 dark:border-gray-700 dark:bg-gray-900">

    <div class="ml-auto w-full max-w-md">

      <div class="mb-4">
        <h3 class="text-sm font-bold uppercase tracking-wide text-gray-950 dark:text-white">
          Summary Purchase Order
        </h3>
      </div>

      <div class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-950">

        {{-- SUBTOTAL --}}
        <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700">
          <span class="text-sm text-gray-500 dark:text-gray-400">
            Subtotal
          </span>

          <span class="text-sm font-semibold text-gray-950 dark:text-white">
            Rp {{ number_format($subtotal, 0, ',', '.') }}
          </span>
        </div>

        {{-- PPN --}}
        @if ($record->ppn_enabled)
          <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700">

            <div class="flex items-center gap-2">
              <span class="text-sm text-gray-500 dark:text-gray-400">
                PPN
              </span>

              <span
                class="rounded-md bg-primary-50 px-2 py-0.5 text-xs font-semibold text-primary-700 dark:bg-primary-500/10 dark:text-primary-400">
                12%
              </span>
            </div>

            <span class="text-sm font-semibold text-gray-950 dark:text-white">
              Rp {{ number_format($ppnAmount, 0, ',', '.') }}
            </span>
          </div>
        @endif

        {{-- TOTAL AFTER PPN --}}
        <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700">

          <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
            Total setelah PPN
          </span>

          <span class="text-sm font-bold text-gray-950 dark:text-white">
            Rp {{ number_format($totalAfterPpn, 0, ',', '.') }}
          </span>
        </div>

        {{-- DISCOUNT --}}
        @if ($discountEnabled)
          <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700">

            <div class="flex items-center gap-2">
              <span class="text-sm text-gray-500 dark:text-gray-400">
                Diskon
              </span>

              <span
                class="rounded-md bg-warning-50 px-2 py-0.5 text-xs font-semibold text-warning-700 dark:bg-warning-500/10 dark:text-warning-400">
                {{ number_format($discountPercent, 2, ',', '.') }}%
              </span>
            </div>

            <span class="text-sm font-semibold text-danger-600 dark:text-danger-400">
              − Rp {{ number_format($discountAmount, 0, ',', '.') }}
            </span>
          </div>
        @endif

        {{-- GRAND TOTAL --}}
        <div class="flex items-center justify-between bg-gray-50 px-4 py-4 dark:bg-gray-900">

          <span class="text-sm font-bold text-gray-950 dark:text-white">
            Total Akhir
          </span>

          <span class="text-lg font-bold text-primary-600 dark:text-primary-400">
            Rp {{ number_format($grandTotal, 0, ',', '.') }}
          </span>

        </div>

      </div>
    </div>
  </div>
</td>
