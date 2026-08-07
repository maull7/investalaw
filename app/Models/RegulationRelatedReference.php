<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegulationRelatedReference extends Model
{
    protected $fillable = [
        'regulation_id',
        'name',
        'number',
        'year',
        'relationship',
    ];

    /** @return BelongsTo<Regulation, RegulationRelatedReference> */
    public function regulation(): BelongsTo
    {
        return $this->belongsTo(Regulation::class);
    }
}
