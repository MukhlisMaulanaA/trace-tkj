<?php

namespace App\Filament\Resources\ProjectSummaries\Pages;

use App\Filament\Resources\ProjectSummaries\ProjectSummaryResource;
use Filament\Resources\Pages\ListRecords;

class ListProjectSummaries extends ListRecords
{
  protected static string $resource = ProjectSummaryResource::class;
}