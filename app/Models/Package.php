<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    protected $fillable = ['name', 'tagline', 'price', 'price_period', 'duration_hours', 'kak_vesta_tokens', 'is_popular', 'benefits', 'is_active', 'sort'];

    protected function casts(): array
    {
        return [
            'duration_hours' => 'integer',
            'kak_vesta_tokens' => 'integer',
            'is_popular' => 'boolean',
            'benefits' => 'array',
            'is_active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function isTrial(): bool
    {
        return (int) $this->price === 0;
    }

    public function userPackages(): HasMany
    {
        return $this->hasMany(UserPackage::class);
    }
}
