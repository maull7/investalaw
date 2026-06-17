<?php

namespace App\Models;

use App\Enums\ComplianceStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['review_id', 'category_id', 'regulation_id', 'compliance_status', 'findings', 'recommendations'])]
class ReviewFinding extends Model
{
    use HasFactory;

    /** @return BelongsTo<Review> */
    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    /** @return BelongsTo<RegulationCategory> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(RegulationCategory::class, 'category_id');
    }

    /** @return BelongsTo<Regulation, ReviewFinding> */
    public function regulation(): BelongsTo
    {
        return $this->belongsTo(Regulation::class, 'regulation_id');
    }

    protected function casts(): array
    {
        return [
            'compliance_status' => ComplianceStatus::class,
        ];
    }
}
