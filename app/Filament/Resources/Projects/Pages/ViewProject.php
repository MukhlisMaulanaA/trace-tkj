<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Resources\Projects\RelationManagers\ProjectProgressRelationManager;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProject extends ViewRecord
{
  protected static string $resource = ProjectResource::class;

  protected function getHeaderActions(): array
  {
    return [
      EditAction::make(),
    ];
  }

  public function getRelationManagers(): array
  {
    return [
      ProjectProgressRelationManager::class,
    ];
  }
}
