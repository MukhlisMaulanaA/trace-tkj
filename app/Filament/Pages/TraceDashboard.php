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
    $poQuery = PurchaseOrder::query()->accessibleBy(Filament::auth()->user())->with('progresses');
    $summaryQuery = ProjectSummary::query()->accessibleBy(Filament::auth()->user())->with('expenditures');

    if ($source !== 'all') {
      $projectQuery->where('project_source', $source);
      $poQuery->whereHas('project', fn($query) => $query->where('project_source', $source));
      $summaryQuery->whereHas('project', fn($query) => $query->where('project_source', $source));
    }

    $projects = $projectQuery->get();
    $purchaseOrders = $poQuery->get();
    $summaries = $summaryQuery->get();
    $period = $this->periodBounds();
    $recentProjects = $projects->sortByDesc('created_at')->take(5);
    $recentOrders = $purchaseOrders->sortByDesc('po_date')->take(5);
    $progresses = $projects->flatMap(fn(Project $project) => $project->progresses)
      ->filter(fn(ProjectProgress $progress) => $this->inPeriod($progress->waktu_progres, $period))
      ->sortByDesc('waktu_progres');
    $invoiceProgresses = $purchaseOrders->flatMap(fn(PurchaseOrder $order) => $order->progresses)
      ->filter(fn(PurchaseOrderProgress $progress) => $this->inPeriod($progress->invoice_date, $period))
      ->sortByDesc('invoice_date');
    $expenditures = $summaries->flatMap(fn(ProjectSummary $summary) => $summary->expenditures)
      ->filter(fn(ProjectExpenditure $expenditure) => $this->inPeriod($expenditure->expenditure_date, $period));
    $latestProgressByProject = $projects->mapWithKeys(fn(Project $project) => [
      $project->id => $project->progresses->sortByDesc('waktu_progres')->first(),
    ]);

    return [
      'sourceLabel' => $this->sourceOptions()[$source] ?? 'Semua Sumber',
      'periodLabel' => $this->periodOptions()[$this->period] ?? 'Bulan ini',
      'kpis' => [
        ['label' => 'Total Project', 'value' => number_format($projects->count()), 'tone' => 'blue', 'url' => ProjectResource::getUrl('index')],
        ['label' => 'Total Purchase Order', 'value' => number_format($purchaseOrders->count()), 'tone' => 'indigo', 'url' => PurchaseOrderResource::getUrl('index')],
        ['label' => 'Contract Value', 'value' => $this->money($summaries->sum(fn(ProjectSummary $summary) => $summary->contract_value)), 'tone' => 'amber', 'url' => ProjectSummaryResource::getUrl('index')],
        ['label' => 'Final Profit', 'value' => $this->money($summaries->sum(fn(ProjectSummary $summary) => $summary->final_profit)), 'tone' => 'emerald', 'url' => ProjectSummaryResource::getUrl('index')],
      ],
      'financial' => [
        'PO / Commitment' => $this->money($purchaseOrders->sum('grand_total')),
        'Pengeluaran' => $this->money($expenditures->sum('amount')),
        'Remaining Budget' => $this->money($summaries->sum(fn(ProjectSummary $summary) => $summary->remaining_budget)),
        'Balance' => $this->money($summaries->sum(fn(ProjectSummary $summary) => $summary->balance)),
      ],
      'progress' => [
        'Project progress' => $progresses->count(),
        'Invoice / PO progress' => $invoiceProgresses->count(),
        'Invoice value' => $this->money($invoiceProgresses->sum('amount')),
        'Invoice percentage' => number_format($invoiceProgresses->sum('percentage'), 2) . '%',
      ],  
      'expenditure' => [
        'Material' => $this->money($expenditures->where('type', ProjectExpenditure::TYPE_MATERIAL)->sum('amount')),
        'Labour' => $this->money($expenditures->where('type', ProjectExpenditure::TYPE_LABOUR)->sum('amount')),
      ],
      'attention' => $summaries->filter(fn(ProjectSummary $summary) => $summary->remaining_budget < 0)
        ->map(fn(ProjectSummary $summary) => [
          'severity' => 'CRITICAL',
          'title' => $summary->project_name ?: $summary->project_id,
          'detail' => 'Sisa Budget project bernilai negatif.',
          'url' => ProjectSummaryResource::getUrl('view', ['record' => $summary]),
        ])->values()->take(5),
      'recentProjects' => $recentProjects->map(fn(Project $project) => [
        'label' => 'Project dibuat',
        'title' => $project->nama_project,
        'date' => $project->created_at?->format('d M Y'),
        'url' => ProjectResource::getUrl('view', ['record' => $project]),
      ]),
      'recentOrders' => $recentOrders->map(fn(PurchaseOrder $order) => [
        'label' => 'PO ditambahkan',
        'title' => $order->po_number,
        'date' => $order->po_date ? Carbon::parse($order->po_date)->format('d M Y') : '-',
        'url' => PurchaseOrderResource::getUrl('view', ['record' => $order]),
      ]),
      'latestProgressByProject' => $latestProgressByProject,
      'hasData' => $projects->isNotEmpty() || $purchaseOrders->isNotEmpty(),
    ];
  }

  public function periodOptions(): array
  {
    return ['month' => 'Bulan ini', 'last_month' => 'Bulan lalu', 'quarter' => 'Quarter ini', 'year' => 'Tahun ini'];
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
    return $date !== null && Carbon::parse($date)->betweenIncluded($period[0], $period[1]);
  }

  private function money(float|int|string $amount): string
  {
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
  }

  private function resetPageState(): void
  {
    unset($this->dashboardData);
  }
}