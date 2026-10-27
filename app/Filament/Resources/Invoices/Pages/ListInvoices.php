<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('yearlySummary')
                ->label('Monitoring Invoices')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->form([
                    Select::make('year')
                        ->label('Monitoring Year')
                        ->options(fn (): array => $this->availableYears())
                        ->default(now()->year)
                        ->required()
                        ->native(false),
                ])
                ->modalSubmitActionLabel('Download Excel')
                ->action(fn (array $data) => redirect()->route('invoices.yearly-summary', ['year' => (int) $data['year']])),
            CreateAction::make(),
        ];
    }

    private function availableYears(): array
    {
        $years = Invoice::accessibleBy(Auth::user())
            ->whereNotNull('created_at')
            ->pluck('created_at')
            ->map(fn ($date): int => Carbon::parse($date)->year)
            ->unique()
            ->all();

        return collect([now()->year, ...$years])
            ->unique()
            ->sortDesc()
            ->mapWithKeys(fn (int $year): array => [$year => (string) $year])
            ->all();
    }
}
