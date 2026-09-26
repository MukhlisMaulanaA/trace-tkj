<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
{
  public function viewAny(User $user): bool
  {
    return true;
  }

  public function view(User $user, PurchaseOrder $purchaseOrder): bool
  {
    return $user->isPusat()
      || $purchaseOrder->project?->project_source === $user->project_scope;
  }

  public function create(User $user): bool
  {
    return true;
  }

  public function update(
    User $user,
    PurchaseOrder $purchaseOrder
  ): bool {
    return $user->isPusat()
      || $purchaseOrder->project?->project_source === $user->project_scope;
  }

  public function delete(
    User $user,
    PurchaseOrder $purchaseOrder
  ): bool {
    return $user->isPusat()
      || $purchaseOrder->project?->project_source === $user->project_scope;
  }
}