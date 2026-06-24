<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalysisReference extends Model
{
    protected $table = 'regulation_analysis_references';

    protected $fillable = [
        'regulation_analysis_id',
        'name',
        'number',
        'year',
        'relationship',
        'sort_order',
    ];

    /** @return BelongsTo<RegulationAnalysis, AnalysisReference> */
    public function analysis(): BelongsTo
    {
        return $this->belongsTo(RegulationAnalysis::class, 'regulation_analysis_id');
    }
}
