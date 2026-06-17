<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['user_id', 'title', 'description', 'file_path', 'status', 'submitted_at'])]
class ReviewDocument extends Model
{
    use HasFactory, SoftDeletes;

    /** @return BelongsTo<User> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsToMany<RegulationCategory> */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(RegulationCategory::class, 'review_document_category', 'review_document_id', 'category_id')
            ->withTimestamps();
    }

    /** @return BelongsToMany<Regulation> */
    public function regulations(): BelongsToMany
    {
        return $this->belongsToMany(Regulation::class, 'review_document_regulation')
            ->withTimestamps();
    }

    /** @return HasOne<Review> */
    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    /** @return HasMany<AiSummary> */
    public function aiSummaries(): HasMany
    {
        return $this->hasMany(AiSummary::class);
    }

    protected function casts(): array
    {
        return [
            'status' => ReviewStatus::class,
            'submitted_at' => 'datetime',
        ];
    }

    public function isDraft(): bool
    {
        return $this->status === ReviewStatus::Draft;
    }

    public function isSubmitted(): bool
    {
        return $this->status === ReviewStatus::Submitted;
    }

    public function canBeReviewed(): bool
    {
        return in_array($this->status, [ReviewStatus::Submitted, ReviewStatus::RevisionRequired]);
    }
}
