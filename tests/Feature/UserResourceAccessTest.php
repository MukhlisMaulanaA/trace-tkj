<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class UserResourceAccessTest extends TestCase
{
  use RefreshDatabase;

  public function test_only_pusat_users_can_access_user_management(): void
  {
    $pusatUser = User::factory()->create(['project_scope' => 'pusat']);
    Auth::login($pusatUser);

    $this->assertTrue(UserResource::canAccess());

    $distrikUser = User::factory()->create(['project_scope' => 'distrik_8']);
    Auth::login($distrikUser);

    $this->assertFalse(UserResource::canAccess());
  }
}