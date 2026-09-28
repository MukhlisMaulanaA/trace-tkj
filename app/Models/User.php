<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable([
  'name',
  'email',
  'password',
  'project_scope',
])]

#[Hidden(['password', 'remember_token'])]

class User extends Authenticatable
{
  /** @use HasFactory<UserFactory> */
  use HasFactory, Notifiable;

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
    ];
  }

  public function isPusat(): bool
  {
    return $this->project_scope === 'pusat';
  }

  public function isDistrik8(): bool
  {
    return $this->project_scope === 'distrik_8';
  }

  protected static function booted(): void
  {
    static::creating(function ($user) {
      if (empty($user->password)) {
        $user->password = Hash::make('Trace_TKJ123');
      }
    });
  }
}
