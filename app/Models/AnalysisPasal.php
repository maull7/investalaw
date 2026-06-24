<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalysisPasal extends Model
{
    protected $table = 'regulation_analysis_pasal';

    protected $fillable = [
        'regulation_analysis_id',
        'pasal',
        'content',
        'type',
        'changes',
        'sort_order',
    ];

    /** @return BelongsTo<RegulationAnalysis, AnalysisPasal> */
    public function analysis(): BelongsTo
    {
        return $this->belongsTo(RegulationAnalysis::class, 'regulation_analysis_id');
    }
}
