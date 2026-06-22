<?php

namespace App\Models;

use App\Enums\ComplianceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartitionAnalysis extends Model
{
    protected $fillable = [
        'document_partition_id',
        'review_document_id',
        'type',
        'summary',
        'findings',
        'compliance_score',
        'compliance_status',
        'raw_response',
        'provider_used',
        'model_used',
    ];

    /** @return BelongsTo<DocumentPartition> */
    public function partition(): BelongsTo
    {
        return $this->belongsTo(DocumentPartition::class, 'document_partition_id');
    }

    /** @return BelongsTo<ReviewDocument> */
    public function reviewDocument(): BelongsTo
    {
        return $this->belongsTo(ReviewDocument::class);
    }

    protected function casts(): array
    {
        return [
            'compliance_status' => ComplianceStatus::class,
        ];
    }
}
