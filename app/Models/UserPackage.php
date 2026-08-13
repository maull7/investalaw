<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPackage extends Model
{
    protected $fillable = ['user_id', 'package_id', 'type', 'status', 'payment_proof', 'confirmed_at', 'trial_ends_at', 'kak_vesta_started_at'];

    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'kak_vesta_started_at' => 'datetime',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
