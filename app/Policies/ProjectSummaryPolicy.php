<?php

namespace App\Policies;

use App\Models\ProjectSummary;
use App\Models\User;

class ProjectSummaryPolicy
{
  public function viewAny(User $user): bool
  {
    return true;
  }

  public function view(
    User $user,
    ProjectSummary $projectSummary
  ): bool {
    return $user->isPusat()
      || $projectSummary->project?->project_source === $user->project_scope;
  }

  public function create(User $user): bool
  {
    return true;
  }

  public function update(
    User $user,
    ProjectSummary $projectSummary
  ): bool {
    return $user->isPusat()
      || $projectSummary->project?->project_source === $user->project_scope;
  }

  public function delete(
    User $user,
    ProjectSummary $projectSummary
  ): bool {
    return $user->isPusat()
      || $projectSummary->project?->project_source === $user->project_scope;
  }
}