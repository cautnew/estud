<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasOwner;
use Database\Factories\StudyPlanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $owner_id
 * @property string $name
 * @property string $exam_board
 * @property string|null $goal
 * @property Carbon|null $deadline
 * @property Carbon|null $exam_date
 * @property bool $is_public
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'exam_board', 'goal', 'deadline', 'exam_date', 'is_public'])]
class StudyPlan extends Model
{
    /** @use HasFactory<StudyPlanFactory> */
    use Auditable, HasFactory, HasOwner;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'deadline' => 'date',
            'exam_date' => 'date',
            'is_public' => 'boolean',
        ];
    }

    /**
     * @return HasMany<StudyPlanSubject, $this>
     */
    public function studyPlanSubjects(): HasMany
    {
        return $this->hasMany(StudyPlanSubject::class)->orderBy('order');
    }

    /**
     * Limit the query to plans published to the public library.
     *
     * @param  Builder<StudyPlan>  $query
     * @return Builder<StudyPlan>
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }
}
