<?php

namespace App\Filament\Resources\ProjectSummaries\Pages;

use App\Filament\Resources\ProjectSummaries\ProjectSummaryResource;
use Filament\Resources\Pages\EditRecord;

class EditProjectSummary extends EditRecord
{
  protected static string $resource = ProjectSummaryResource::class;
}