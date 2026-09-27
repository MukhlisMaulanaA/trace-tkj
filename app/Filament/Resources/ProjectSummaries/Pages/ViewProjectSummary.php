<?php

namespace App\Filament\Resources\ProjectSummaries\Pages;

use App\Filament\Resources\ProjectSummaries\ProjectSummaryResource;
use App\Models\ProjectSummary;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Validation\Rule;

class ViewProjectSummary extends ViewRecord
{
  protected static string $resource = ProjectSummaryResource::class;

  protected string $view = 'filament.resources.project-summaries.pages.view-project-summary';

  public bool $isEditingProfit = false;

  public string $draftProfitMode = '';

  public float|int|string|null $draftProfitPercentage = null;

  public float|int|string|null $draftNominalProfit = null;

  public function mount(int|string $record): void
  {
    parent::mount($record);

    $this->resetProfitDraft();
  }

  public function startProfitEditing(): void
  {
    $this->isEditingProfit = true;
  }

  public function cancelProfitChanges(): void
  {
    $this->resetProfitDraft();
  }

  public function saveProfitChanges(): void
  {
    abort_unless(static::getResource()::canEdit($this->getRecord()), 403);

    $validatedProfit = validator(
      [
        'profit_mode' => $this->draftProfitMode,
        'profit_percentage' => $this->draftProfitPercentage,
        'nominal_profit' => $this->draftNominalProfit,
      ],
      [
        'profit_mode' => [
          'required',
          Rule::in([
            ProjectSummary::PROFIT_MODE_PERCENTAGE,
            ProjectSummary::PROFIT_MODE_NOMINAL,
          ]),
        ],
        'profit_percentage' => [
          'required_if:profit_mode,percentage',
          'numeric',
          'min:0',
          'max:100',
        ],
        'nominal_profit' => [
          'required_if:profit_mode,nominal',
          'numeric',
          'min:0',
        ],
      ],
    )->validate();

    $record = $this->getRecord();

    $record->profit_mode = $validatedProfit['profit_mode'];

    if ($validatedProfit['profit_mode'] === ProjectSummary::PROFIT_MODE_NOMINAL) {
      $record->nominal_profit = max(
        0,
        (float) ($validatedProfit['nominal_profit'] ?? 0)
      );

      $record->syncProfitValues();
    } else {
      $record->profit_percentage = max(
        0,
        min(100, (float) ($validatedProfit['profit_percentage'] ?? 0))
      );

      $record->syncProfitValues();
    }

    $record->save();

    $this->record = $record->fresh();

    $this->resetProfitDraft();
  }

  public function getDraftCalculatedPercentageProperty(): float
  {
    $finalContractValue = (float) $this->getRecord()->final_contract_value;

    if ($finalContractValue <= 0) {
      return 0;
    }

    if ($this->draftProfitMode === ProjectSummary::PROFIT_MODE_NOMINAL) {
      return round(
        ((float) ($this->draftNominalProfit ?? 0) / $finalContractValue) * 100,
        2
      );
    }

    return round(
      (float) ($this->draftProfitPercentage ?? 0),
      2
    );
  }

  public function getDraftFinalProfitProperty(): float
  {
    if ($this->draftProfitMode === ProjectSummary::PROFIT_MODE_NOMINAL) {
      return round((float) ($this->draftNominalProfit ?? 0), 0);
    }

    return round(
      (float) $this->getRecord()->final_contract_value * ((float) ($this->draftProfitPercentage ?? 0) / 100),
      0,
    );
  }

  public function getDraftRemainingBudgetProperty(): float
  {
    return (float) $this->getRecord()->final_contract_value
      - (float) $this->getRecord()->ml_expenditure
      - $this->draftFinalProfit;
  }

  public function getDraftBalanceProperty(): float
  {
    return $this->draftFinalProfit
      + $this->draftRemainingBudget
      + (float) $this->getRecord()->ml_expenditure;
  }

  protected function resetProfitDraft(): void
  {
    $this->draftProfitMode = (string) $this->getRecord()->profit_mode;
    $this->draftProfitPercentage = (float) $this->getRecord()->profit_percentage;
    $this->draftNominalProfit = (float) $this->getRecord()->nominal_profit;
    $this->isEditingProfit = false;
  }

  protected function getHeaderActions(): array
  {
    return [EditAction::make()];
  }
}