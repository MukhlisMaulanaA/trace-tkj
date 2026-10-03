<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

class ProjectReview extends Page
{
  protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;
  protected static ?string $navigationLabel = 'Project Review';
  protected static ?int $navigationSort = 1;
  protected static string $routePath = 'project-review';
  protected string $view = 'filament.pages.project-review';

  protected static string | UnitEnum | null $navigationGroup = 'Analysis';

  public string $search = '';
  public string $source = 'all';
  public string $completion = 'all';
  public string $sort = 'project';
  public string $direction = 'asc';

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

  public function sortOptions(): array
  {
    return [
      'project' => 'Nama project',
      'work_progress' => 'Progress pekerjaan',
      'payment_progress' => 'Progress pembayaran',
      'po_value' => 'Nilai PO',
      'latest_activity' => 'Aktivitas terbaru',
    ];
  }

  public function completionOptions(): array
  {
    return [
      'all' => 'Semua project',
      'active' => 'Project berjalan',
      'completed' => 'Project Completed',
    ];
  }

  public function directionOptions(): array
  {
    return [
      'asc' => 'Naik',
      'desc' => 'Turun',
    ];
  }

  public function reviewCards(): Collection
  {
    $source = $this->canChooseSource() ? $this->source : 'distrik_8';
    $query = Project::query()
      ->accessibleBy(Filament::auth()->user())
      ->with(['progresses', 'purchaseOrders.progresses'])
      ->when($source !== 'all', fn($query) => $query->where('project_source', $source))
      ->when(trim($this->search) !== '', function ($query): void {
        $search = '%' . trim($this->search) . '%';

        $query->where(function ($query) use ($search): void {
          $query
            ->where('id', 'like', $search)
            ->orWhere('nama_project', 'like', $search)
            ->orWhere('kustomer', 'like', $search)
            ->orWhere('lokasi', 'like', $search)
            ->orWhereHas('purchaseOrders', fn($query) => $query->where('po_number', 'like', $search));
        });
      });

    $cards = $query->get()
      ->map(fn(Project $project): array => $this->cardFor($project))
      ->when($this->completion !== 'all', fn(Collection $cards): Collection => $cards->filter(
        fn(array $card): bool => $this->completion === 'completed'
          ? $card['is_completed']
          : ! $card['is_completed'],
      ))
      ->sortBy(
        fn(array $card): string|int|float => $card[$this->sort] ?? $card['project_name'],
        SORT_NATURAL,
        $this->direction === 'desc',
      )
      ->sortBy('is_completed')
      ->values();

    return $cards;
  }

  public function money(float|int|string $amount): string
  {
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
  }

  public function percentage(float|int|string $value): string
  {
    return number_format((float) $value, 1, ',', '.') . '%';
  }

  public function projectUrl(Project $project): string
  {
    return ProjectResource::getUrl('view', ['record' => $project]);
  }

  public function purchaseOrderUrl($purchaseOrder): string
  {
    return PurchaseOrderResource::getUrl('view', ['record' => $purchaseOrder]);
  }

  private function cardFor(Project $project): array
  {
    $latestWorkProgress = $project->progresses
      ->where('is_system', false)
      ->sortByDesc('waktu_progres')
      ->first() ?? $project->progresses->sortByDesc('waktu_progres')->first();
    $purchaseOrders = $project->purchaseOrders;
    $poValue = (float) $purchaseOrders->sum('grand_total');
    $invoiceValue = (float) $purchaseOrders
      ->flatMap(fn($purchaseOrder) => $purchaseOrder->progresses)
      ->sum('amount');
    $paymentProgress = $poValue > 0 ? ($invoiceValue / $poValue) * 100 : 0;
    $latestInvoice = $purchaseOrders
      ->flatMap(fn($purchaseOrder) => $purchaseOrder->progresses)
      ->sortByDesc('invoice_date')
      ->first();
    $latestActivity = collect([
      $latestWorkProgress?->waktu_progres,
      $latestInvoice?->invoice_date,
      $project->created_at,
    ])->filter()->map(fn($date) => Carbon::parse($date))->sortDesc()->first();
    $workStatus = $this->workStatus((float) ($latestWorkProgress?->persentase ?? 0));
    $purchaseOrderCards = $purchaseOrders->map(fn($purchaseOrder): array => $this->purchaseOrderCard($purchaseOrder));

    return [
      'project' => $project,
      'project_id' => $project->id,
      'project_name' => $project->nama_project ?: $project->id,
      'customer' => $project->kustomer ?: '-',
      'location' => $project->lokasi ?: '-',
      'source' => $project->project_source ?: '-',
      'work_progress' => (float) ($latestWorkProgress?->persentase ?? 0),
      'work_date' => $latestWorkProgress?->waktu_progres,
      'work_note' => $latestWorkProgress?->keterangan ?: 'Belum ada update pekerjaan.',
      'work_status' => $workStatus,
      'is_completed' => (float) ($latestWorkProgress?->persentase ?? 0) >= 100 && $paymentProgress >= 100,
      'po_count' => $purchaseOrders->count(),
      'invoice_count' => $purchaseOrderCards->sum('invoice_count'),
      'po_value' => $poValue,
      'payment_value' => $invoiceValue,
      'payment_progress' => $paymentProgress,
      'payment_remaining' => $poValue - $invoiceValue,
      'latest_invoice' => $latestInvoice?->invoice_date,
      'latest_activity' => $latestActivity?->timestamp ?? 0,
      'latest_activity_date' => $latestActivity,
      'purchase_orders' => $purchaseOrderCards,
    ];
  }

  private function workStatus(float $progress): array
  {
    return match (true) {
      $progress >= 100 => ['label' => 'Selesai', 'tone' => 'success'],
      $progress > 0 => ['label' => 'Berjalan', 'tone' => 'info'],
      default => ['label' => 'Awal', 'tone' => 'warning'],
    };
  }

  private function purchaseOrderCard($purchaseOrder): array
  {
    $invoiceValue = (float) $purchaseOrder->progresses->sum('amount');
    $poValue = (float) $purchaseOrder->grand_total;
    $paymentProgress = $poValue > 0 ? ($invoiceValue / $poValue) * 100 : 0;
    $status = match (true) {
      $paymentProgress >= 100 => ['label' => 'Selesai', 'tone' => 'success'],
      $paymentProgress > 0 => ['label' => 'Berjalan', 'tone' => 'info'],
      default => ['label' => 'Awal', 'tone' => 'warning'],
    };

    return [
      'record' => $purchaseOrder,
      'url' => $this->purchaseOrderUrl($purchaseOrder),
      'po_number' => $purchaseOrder->po_number ?: $purchaseOrder->po_code,
      'po_value' => $poValue,
      'invoice_value' => $invoiceValue,
      'invoice_count' => $purchaseOrder->progresses->count(),
      'payment_progress' => $paymentProgress,
      'payment_remaining' => $poValue - $invoiceValue,
      'status' => $status,
      'invoices' => $purchaseOrder->progresses->sortByDesc('invoice_date')->values(),
    ];
  }
}