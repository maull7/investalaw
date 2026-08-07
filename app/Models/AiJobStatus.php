<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AiJobStatus extends Model
{
    protected $table = 'ai_job_status';

    protected $fillable = [
        'model_type',
        'model_id',
        'action',
        'status',
        'message',
    ];

    /** @return MorphTo<Model, AiJobStatus> */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public static function begin(Model $model, string $action): self
    {
        return static::updateOrCreate(
            ['model_type' => $model->getMorphClass(), 'model_id' => $model->getKey(), 'action' => $action],
            ['status' => 'processing', 'message' => null],
        );
    }

    public function markDone(?string $message = null): void
    {
        $this->update(['status' => 'done', 'message' => $message]);
    }

    public function markFailed(string $message): void
    {
        $this->update(['status' => 'error', 'message' => $message]);
    }
}
