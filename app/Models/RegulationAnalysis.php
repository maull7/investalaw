<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegulationAnalysis extends Model
{
    protected $fillable = [
        'regulation_id',
        'context',
        'comparison_insights',
        'change_analysis',
        'revocation_analysis',
        'changes_summary',
        'key_points',
        'related_data',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'related_data' => 'array',
            'key_points' => 'array',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<Regulation, RegulationAnalysis> */
    public function regulation(): BelongsTo
    {
        return $this->belongsTo(Regulation::class);
    }

    /** @return HasMany<AnalysisPasal, RegulationAnalysis> */
    public function pasal(): HasMany
    {
        return $this->hasMany(AnalysisPasal::class, 'regulation_analysis_id')->orderBy('sort_order');
    }

    /** @return HasMany<AnalysisReference, RegulationAnalysis> */
    public function references(): HasMany
    {
        return $this->hasMany(AnalysisReference::class, 'regulation_analysis_id')->orderBy('sort_order');
    }
}
