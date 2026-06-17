<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'level'])]
class RegulationType extends Model
{
    use HasFactory, SoftDeletes;

    /** @return HasMany<Regulation> */
    public function regulations(): HasMany
    {
        return $this->hasMany(Regulation::class);
    }

    public function levelBadgeColor(): string
    {
        return match ($this->level) {
            1 => 'red',
            2 => 'orange',
            3 => 'yellow',
            4 => 'blue',
            5 => 'green',
            default => 'gray',
        };
    }

    protected function casts(): array
    {
        return [
            'level' => 'integer',
        ];
    }
}
