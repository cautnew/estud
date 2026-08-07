<?php

namespace App\Models;

use Database\Factories\ExamSubjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $exam_notice_id
 * @property int $subject_id
 * @property int $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['subject_id', 'order'])]
class ExamSubject extends Model
{
    /** @use HasFactory<ExamSubjectFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<ExamNotice, $this>
     */
    public function examNotice(): BelongsTo
    {
        return $this->belongsTo(ExamNotice::class);
    }

    /**
     * @return BelongsTo<Subject, $this>
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * @return HasMany<ExamTopic, $this>
     */
    public function examTopics(): HasMany
    {
        return $this->hasMany(ExamTopic::class)->orderBy('order');
    }
}
