<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['regulation_id', 'name', 'document_type', 'file_path'])]
class RegulationDocument extends Model
{
    use HasFactory;

    /** @return BelongsTo<Regulation, RegulationDocument> */
    public function regulation(): BelongsTo
    {
        return $this->belongsTo(Regulation::class);
    }
}
