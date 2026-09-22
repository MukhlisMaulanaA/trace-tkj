<?php

namespace App\Filament\Resources\ProjectSummaries\Pages;

use App\Filament\Resources\ProjectSummaries\ProjectSummaryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProjectSummary extends ViewRecord
{
  protected static string $resource = ProjectSummaryResource::class;

  protected string $view = 'filament.resources.project-summaries.pages.view-project-summary';

  protected function getHeaderActions(): array
  {
    return [EditAction::make()];
  }
}