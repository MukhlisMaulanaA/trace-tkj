<?php

namespace App\Models;

use App\Models\ProjectProgress;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
  use HasFactory;

  // Tentukan primary key dan matikan auto increment
  protected $primaryKey = 'id';
  public $incrementing = false;
  protected $keyType = 'string';

  protected $fillable = [
    'id',
    'nama_project',
    'kustomer',
    'lokasi',
    'nomor_quotation',
    'pic',
  ];

  /**
   * Auto-generate kustom ID saat membuat record baru.
   * Format: P + YY + Kode Bulan (A-L) + Nomor Urut 3 Digit (001)
   */
  protected static function booted(): void
  {
    static::creating(function ($project) {
      if (empty($project->id)) {
        $project->id = static::generateCustomId();
      }
    });

    static::created(function ($project) {
      $project->progresses()->create([
        'waktu_progres' => $project->created_at,
        'persentase' => 0,
        'keterangan' => 'Project Created',
        'is_system' => true,
      ]);
    });
  }

  public static function generateCustomId(): string
  {
    $prefix = 'P';
    $year = date('y'); // 2 Digit tahun (misal: 26)

    // Konversi bulan 1-12 menjadi Huruf A-L (1 = A, 8 = H, 12 = L)
    $monthNum = (int) date('n');
    $monthLetter = chr(64 + $monthNum);

    $prefixCode = $prefix . $year . $monthLetter; // Contoh: P26H

    // Cari ID terakhir pada bulan & tahun yang sama untuk menentukan nomor urut
    $lastProject = static::where('id', 'LIKE', $prefixCode . '%')
      ->orderBy('id', 'desc')
      ->first();

    if ($lastProject) {
      // Ambil 3 digit terakhir dari ID lalu tambahkan 1
      $lastSequence = (int) substr($lastProject->id, -3);
      $newSequence = sprintf('%03d', $lastSequence + 1);
    } else {
      // Jika belum ada data di bulan ini, mulai dari 001
      $newSequence = '001';
    }

    return $prefixCode . $newSequence;
  }

  public function progresses(): HasMany
  {
    return $this->hasMany(ProjectProgress::class);
  }

  public function purchaseOrders(): HasMany
  {
    return $this->hasMany(PurchaseOrder::class, 'project_id', 'id');
  }
}