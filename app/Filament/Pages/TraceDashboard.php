<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ProjectSummaries\ProjectSummaryResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use App\Models\Project;
use App\Models\ProjectExpenditure;
use App\Models\ProjectProgress;
use App\Models\ProjectSummary;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderProgress;
use App\Models\User;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class TraceDashboard extends Dashboard
{
  protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;
  protected static ?string $navigationLabel = 'Dashboard';
  protected static ?int $navigationSort = -2;
  protected static string $routePath = '/';
  protected string $view = 'filament.pages.trace-dashboard';

  public string $source = 'all';
  public string $period = 'month';

  public function updatedSource(): void
  {
    $this->resetPageState();
  }

  public function updatedPeriod(): void
  {
    $this->resetPageState();
  }

  public function refreshDashboard(): void
  {
    $this->resetPageState();
  }

  public function canChooseSource(): bool
  {
    /** @var User|null $user */
    $user = Filament::auth()->user();

    return $user?->isPusat() ?? false;
  }

  public function sourceOptions(): array
  {
    return $this->canChooseSource()
      ? ['all' => 'Semua Sumber', 'pusat' => 'Pusat', 'distrik_8' => 'Distrik 8']
      : ['distrik_8' => 'Distrik 8'];
  }

  public function dashboardData(): array
  {
    $source = $this->canChooseSource() ? $this->source : 'distrik_8';
    $projectQuery = Project::query()
      ->accessibleBy(Filament::auth()->user())
      ->with(['progresses', 'summary.expenditures', 'purchaseOrders.progresses']);
    $poQuery = PurchaseOrder::query()
      ->where('type', 'project')
      ->accessibleBy(Filament::auth()->user())
      ->with('progresses');
    $summaryQuery = ProjectSummary::query()->accessibleBy(Filament::auth()->user())->with(['project', 'expenditures']);

    if ($source !== 'all') {
      $projectQuery->where('project_source', $source);
      $poQuery->whereHas('project', fn($query) => $query->where('project_source', $source));
      $summaryQuery->whereHas('project', fn($query) => $query->where('project_source', $source));
    }

    $period = $this->periodBounds();
    $allProjects = $projectQuery->get();
    $allPurchaseOrders = $poQuery->get();
    $allSummaries = $summaryQuery->get();
    $projects = $allProjects
      ->filter(fn($project) => $this->inPeriod($project->created_at, $period))
      ->values();
    $purchaseOrders = $allPurchaseOrders
      ->filter(fn($order) => $this->inPeriod($order->po_date, $period))
      ->values();
    $summaries = $allSummaries
      ->filter(fn($summary) => $this->inPeriod($summary->project?->created_at, $period))
      ->values();
    $recentProjects = $projects->sortByDesc('created_at')->take(5);
    $recentOrders = $purchaseOrders->sortByDesc('po_date')->take(5);
    $progresses = $projects->flatMap(fn(Project $project) => $project->progresses)
      ->filter(fn(ProjectProgress $progress) => $this->inPeriod($progress->waktu_progres, $period))
      ->sortByDesc('waktu_progres');
    $allInvoiceProgresses = $purchaseOrders->flatMap(fn(PurchaseOrder $order) => $order->progresses);
    $chartInvoiceProgresses = $allPurchaseOrders->flatMap(fn(PurchaseOrder $order) => $order->progresses);
    $invoiceProgresses = $purchaseOrders->flatMap(fn(PurchaseOrder $order) => $order->progresses)
      ->filter(fn(PurchaseOrderProgress $progress) => $this->inPeriod($progress->invoice_date, $period))
      ->sortByDesc('invoice_date');
    $invoiceValue = (float) $invoiceProgresses->sum('amount');
    $cumulativeInvoiceValue = (float) $allInvoiceProgresses->sum('amount');
    $commitmentValue = (float) $purchaseOrders->sum('grand_total');
    $invoicePercentage = $commitmentValue > 0
      ? ($cumulativeInvoiceValue / $commitmentValue) * 100
      : 0;
    $expenditures = $summaries->flatMap(fn(ProjectSummary $summary) => $summary->expenditures)
      ->filter(fn(ProjectExpenditure $expenditure) => $this->inPeriod($expenditure->expenditure_date, $period));
    $allExpenditures = $allSummaries->flatMap(fn(ProjectSummary $summary) => $summary->expenditures);
    $chartActivityDates = collect([
      ...$allPurchaseOrders->pluck('po_date')->all(),
      ...$chartInvoiceProgresses->pluck('invoice_date')->all(),
      ...$allExpenditures->pluck('expenditure_date')->all(),
      ...$allProjects->pluck('created_at')->all(),
    ])->filter()->map(fn($date) => Carbon::parse($date)->startOfMonth());
    $chartEnd = now()->startOfMonth();
    $chartStart = $chartActivityDates->isNotEmpty() ? $chartActivityDates->min() : $chartEnd;
    $chartMonthCount = max(12, (int) $chartStart->diffInMonths($chartEnd) + 1);
    $chart = collect(range($chartMonthCount - 1, 0))->map(function (int $monthsAgo) use ($chartEnd, $allInvoiceProgresses, $allExpenditures, $allSummaries, $allPurchaseOrders): array {
      $month = $chartEnd->copy()->subMonths($monthsAgo);
      $monthKey = $month->format('Y-m');

      return [
        'label' => $month->format('M'),
        'po' => (float) $allPurchaseOrders->filter(fn($order) => $order->po_date && Carbon::parse($order->po_date)->format('Y-m') === $monthKey)->sum('grand_total'),
        'profit' => (float) $allSummaries->filter(fn($summary) => $summary->project?->created_at && Carbon::parse($summary->project->created_at)->format('Y-m') === $monthKey)->sum(fn($summary) => $summary->final_profit),
        'expenditure' => (float) $allExpenditures->filter(fn($expenditure) => Carbon::parse($expenditure->expenditure_date)->format('Y-m') === $monthKey)->sum('amount'),
      ];
    });
    $chartMax = max(1, (float) $chart->max(fn(array $point): float => max($point['po'], $point['profit'], $point['expenditure'])));
    $chartRanges = collect([
      '3m' => $chart->slice(-3)->values(),
      '6m' => $chart->slice(-6)->values(),
      '1y' => $chart->slice(-12)->values(),
      'all' => $chart->values(),
    ])->mapWithKeys(fn(Collection $points, string $key) => [$key => $this->chartPayload($points)]);
    $latestProgressByProject = $projects->mapWithKeys(fn(Project $project) => [
      $project->id => $project->progresses->sortByDesc('waktu_progres')->first(),
    ]);

    $attention = $this->staleProjectAttention($allProjects)
      ->concat($this->stalePurchaseOrderAttention($allPurchaseOrders))
      ->sortBy('date')
      ->values()
      ->take(8);
    $highlights = $this->projectHighlights($summaries);
    $lastMonth = [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()];
    $previousProjects = $projects->filter(fn($project) => $project->created_at && Carbon::parse($project->created_at)->lte($lastMonth[1]));
    $previousOrders = $purchaseOrders->filter(fn($order) => $order->po_date && Carbon::parse($order->po_date)->lte($lastMonth[1]));
    $previousSummaries = $summaries->filter(fn($summary) => $summary->project?->created_at && Carbon::parse($summary->project->created_at)->lte($lastMonth[1]));
    $materialTotal = (float) $expenditures->where('type', ProjectExpenditure::TYPE_MATERIAL)->sum('amount');
    $labourTotal = (float) $expenditures->where('type', ProjectExpenditure::TYPE_LABOUR)->sum('amount');
    $expenditureTotal = $materialTotal + $labourTotal;
    $profitTotal = max(0, (float) $summaries->sum(fn(ProjectSummary $summary) => $summary->final_profit));
    $remainingBudgetTotal = max(0, (float) $summaries->sum(fn(ProjectSummary $summary) => $summary->remaining_budget));
    $financialMixTotal = max(1, $expenditureTotal + $profitTotal + $remainingBudgetTotal);

    return [
      'sourceLabel' => $this->sourceOptions()[$source] ?? 'Semua Sumber',
      'periodLabel' => $this->periodOptions()[$this->period] ?? 'Bulan ini',
      'kpis' => [
        ['label' => 'Total Project', 'value' => number_format($projects->count()), 'comparison' => $this->comparison($projects->count(), $previousProjects->count(), 'count'), 'tone' => 'blue', 'url' => ProjectResource::getUrl('index')],
        ['label' => 'Total PO', 'value' => number_format($purchaseOrders->count()), 'comparison' => $this->comparison($purchaseOrders->count(), $previousOrders->count(), 'count'), 'tone' => 'indigo', 'url' => PurchaseOrderResource::getUrl('index')],
        ['label' => 'Nilai PO', 'value' => $this->compactMoney($commitmentValue), 'comparison' => $this->comparison($commitmentValue, $previousOrders->sum('grand_total'), 'percentage'), 'tone' => 'amber', 'url' => ProjectSummaryResource::getUrl('index')],
        ['label' => 'Profit', 'value' => $this->compactMoney($summaries->sum(fn(ProjectSummary $summary) => $summary->final_profit)), 'comparison' => $this->comparison($summaries->sum(fn(ProjectSummary $summary) => $summary->final_profit), $previousSummaries->sum(fn(ProjectSummary $summary) => $summary->final_profit), 'percentage'), 'tone' => 'emerald', 'url' => ProjectSummaryResource::getUrl('index')],
      ],
      'financial' => [
        'Total Nilai PO' => $this->money($commitmentValue),
        'Pengeluaran' => $this->money($expenditures->sum('amount')),
        'Sisa Budget' => $this->money($summaries->sum(fn(ProjectSummary $summary) => $summary->remaining_budget)),
        'Balance' => $this->money($summaries->sum(fn(ProjectSummary $summary) => $summary->balance)),
      ],
      'progress' => [
        'Project progress' => $progresses->count(),
        'Invoice / PO progress' => $invoiceProgresses->count(),
        'Total Nilai Invoice' => $this->money($invoiceValue),
        'Persentase Invoice' => number_format($invoicePercentage, 2) . '%',
        'Monthly Invoice' => $commitmentValue > 0 ? number_format(($invoiceValue / $commitmentValue) * 100, 2) . '%' : '0.00%',
      ],  
      'chart' => [
        'points' => $chart,
        'max' => $chartMax,
      ],
      'chartRanges' => $chartRanges,
      'financialMix' => [
        ['label' => 'Pengeluaran', 'percentage' => round(($expenditureTotal / $financialMixTotal) * 100), 'tone' => 'red'],
        ['label' => 'Profit', 'percentage' => round(($profitTotal / $financialMixTotal) * 100), 'tone' => 'green'],
        ['label' => 'Sisa Budget', 'percentage' => round(($remainingBudgetTotal / $financialMixTotal) * 100), 'tone' => 'blue'],
      ],
      'expenditure' => [
        'Material' => ['amount' => $this->money($materialTotal), 'percentage' => $expenditureTotal > 0 ? round(($materialTotal / $expenditureTotal) * 100) : 0],
        'Labour' => ['amount' => $this->money($labourTotal), 'percentage' => $expenditureTotal > 0 ? round(($labourTotal / $expenditureTotal) * 100) : 0],
      ],
      'attention' => $attention,
      'highlights' => $highlights,
      'recentProjects' => $recentProjects->map(fn(Project $project) => [
        'label' => 'Project dibuat',
        'title' => $project->nama_project,
        'date' => $project->created_at?->format('d M Y'),
        'location' => $project->lokasi ?: 'Lokasi tidak tersedia',
        'url' => ProjectResource::getUrl('view', ['record' => $project]),
      ]),
      'recentOrders' => $recentOrders->map(fn(PurchaseOrder $order) => [
        'label' => 'PO ditambahkan',
        'title' => $order->po_number,
        'date' => $order->po_date ? Carbon::parse($order->po_date)->format('d M Y') : '-',
        'progress' => $this->purchaseOrderProgressPercentage($order),
        'value' => $this->money($order->grand_total),
        'url' => PurchaseOrderResource::getUrl('view', ['record' => $order]),
      ]),
      'latestProgressByProject' => $latestProgressByProject,
      'hasData' => $projects->isNotEmpty() || $purchaseOrders->isNotEmpty(),
    ];
  }

  public function periodOptions(): array
  {
    return ['month' => 'Bulan ini', 'last_month' => 'Bulan lalu', 'quarter' => 'Quarter ini', 'year' => 'Tahun ini', 'all' => 'All Time'];
  }

  private function periodBounds(): array
  {
    $now = now();
    return match ($this->period) {
      'last_month' => [$now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth()],
      'quarter' => [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()],
      'year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
      default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
    };
  }

  private function inPeriod(null|Carbon|string $date, array $period): bool
  {
    if ($this->period === 'all') {
      return $date !== null;
    }

    return $date !== null && Carbon::parse($date)->betweenIncluded($period[0], $period[1]);
  }

  private function stalePurchaseOrderAttention(Collection $purchaseOrders): Collection
  {
    $staleSince = now()->subDays(30);

    return $purchaseOrders
      ->filter(function (PurchaseOrder $purchaseOrder) use ($staleSince): bool {
        $invoiceTotal = (float) $purchaseOrder->progresses->sum('amount');
        $grandTotal = (float) $purchaseOrder->grand_total;

        if ($grandTotal > 0 && $invoiceTotal >= $grandTotal) {
          return false;
        }

        $lastProgress = $purchaseOrder->progresses->sortByDesc('invoice_date')->first();
        $lastActivity = $lastProgress?->invoice_date ?? $purchaseOrder->po_date;

        return $lastActivity !== null && Carbon::parse($lastActivity)->lte($staleSince);
      })
      ->map(function (PurchaseOrder $purchaseOrder): array {
        $hasProgress = $purchaseOrder->progresses->isNotEmpty();
        $lastProgress = $purchaseOrder->progresses->sortByDesc('invoice_date')->first();
        $lastActivity = $lastProgress?->invoice_date ?? $purchaseOrder->po_date;

        return [
          'severity' => 'WARNING',
          'title' => $purchaseOrder->po_number,
          'date' => Carbon::parse($lastActivity)->timestamp,
          'detail' => $hasProgress
            ? 'Tidak ada aktivitas pembayaran selama 30 hari.'
            : 'Belum ada aktivitas pembayaran selama 30 hari.',
          'url' => PurchaseOrderResource::getUrl('view', ['record' => $purchaseOrder]),
        ];
      });
  }

  private function staleProjectAttention(Collection $projects): Collection
  {
    $staleSince = now()->subDays(30);

    return $projects
      ->filter(function (Project $project) use ($staleSince): bool {
        $lastProgress = $project->progresses->sortByDesc('waktu_progres')->first();
        $lastActivity = $lastProgress?->waktu_progres ?? $project->created_at;

        return $lastActivity !== null && Carbon::parse($lastActivity)->lte($staleSince);
      })
      ->map(function (Project $project): array {
        $lastProgress = $project->progresses->sortByDesc('waktu_progres')->first();
        $lastActivity = $lastProgress?->waktu_progres ?? $project->created_at;

        return [
          'severity' => 'WARNING',
          'title' => $project->nama_project,
          'date' => Carbon::parse($lastActivity)->timestamp,
          'detail' => $lastProgress
            ? 'Tidak ada project progress selama 30 hari.'
            : 'Belum ada project progress selama 30 hari.',
          'url' => ProjectResource::getUrl('view', ['record' => $project]),
        ];
      });
  }

  private function projectHighlights(Collection $summaries): Collection
  {
    $withProjects = $summaries->filter(fn(ProjectSummary $summary) => $summary->project !== null);
    $highestProfit = $withProjects->sortByDesc(fn(ProjectSummary $summary) => $summary->final_profit)->first();
    $highestExpenditure = $withProjects->sortByDesc(fn(ProjectSummary $summary) => $summary->ml_expenditure)->first();

    return collect([
      $highestProfit ? [
        'label' => 'PROFIT TERTINGGI',
        'title' => $highestProfit->project_name,
        'location' => $highestProfit->project->lokasi,
        'value' => $this->money($highestProfit->final_profit),
        'detail' => 'Profit project tertinggi.',
        'tone' => 'success',
        'url' => ProjectSummaryResource::getUrl('view', ['record' => $highestProfit]),
      ] : null,
      $highestExpenditure ? [
        'label' => 'PENGELUARAN TERBANYAK',
        'title' => $highestExpenditure->project_name,
        'location' => $highestExpenditure->project->lokasi,
        'value' => $this->money($highestExpenditure->ml_expenditure),
        'detail' => 'Pengeluaran project tertinggi.',
        'tone' => 'danger',
        'url' => ProjectSummaryResource::getUrl('view', ['record' => $highestExpenditure]),
      ] : null,
    ])->filter()->values();
  }

  private function comparison(float|int $current, float|int $previous, string $unit): array
  {
    $current = (float) $current;
    $previous = (float) $previous;
    $difference = $current - $previous;
    $direction = $difference > 0 ? 'up' : ($difference < 0 ? 'down' : 'same');
    $formattedDifference = $unit === 'percentage'
      ? number_format($previous !== 0.0 ? abs($difference / $previous) * 100 : ($current !== 0.0 ? 100 : 0), 1) . '%'
      : number_format(abs($difference));

    $label = match ($direction) {
      'up' => ' ' . $formattedDifference,
      'down' => ' ' . $formattedDifference,
      default => 'Tidak berubah',
    };

    return [
      'direction' => $direction,
      'label' => $label,
    ];
  }

  private function chartPayload(Collection $points): array
  {
    $max = max(1, (float) $points->max(fn(array $point): float => max($point['po'], $point['profit'], $point['expenditure'])));

    return [
      'points' => $points->values(),
      'max' => $max,
      'axis' => [$this->money($max), $this->money($max / 2), 'Rp 0'],
    ];
  }

  private function purchaseOrderProgressPercentage(PurchaseOrder $purchaseOrder): string
  {
    $grandTotal = (float) $purchaseOrder->grand_total;

    if ($grandTotal <= 0) {
      return '0%';
    }

    return number_format(min(100, ($purchaseOrder->progresses->sum('amount') / $grandTotal) * 100), 0) . '%';
  }

  private function projectStatus(Project $project): string
  {
    $progress = $project->progresses->sortByDesc('waktu_progres')->first();

    return match (true) {
      !$progress => 'Belum dimulai',
      (float) $progress->persentase >= 100 => 'Selesai',
      Carbon::parse($progress->waktu_progres)->lt(now()->subMonth()) => 'Perlu review',
      default => 'On Track',
    };
  }

  private function projectStatusTone(Project $project): string
  {
    return match ($this->projectStatus($project)) {
      'Selesai', 'On Track' => 'success',
      'Perlu review' => 'warning',
      default => 'neutral',
    };
  }

  private function money(float|int|string $amount): string
  {
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
  }

  private function compactMoney(float|int|string $amount): string
  {
    $amount = (float) $amount;

    return match (true) {
      abs($amount) >= 1_000_000_000_000 => 'Rp ' . number_format($amount / 1_000_000_000_000, 2, ',', '.') . 'T',
      abs($amount) >= 1_000_000_000 => 'Rp ' . number_format($amount / 1_000_000_000, 2, ',', '.') . 'M',
      abs($amount) >= 1_000_000 => 'Rp ' . number_format($amount / 1_000_000, 2, ',', '.') . 'JT',
      abs($amount) >= 1_000 => 'Rp ' . number_format($amount / 1_000, 2, ',', '.') . 'RB',
      default => $this->money($amount),
    };
  }

  private function resetPageState(): void
  {
    unset($this->dashboardData);
  }
}