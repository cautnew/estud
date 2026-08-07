<?php

namespace App\Models;

use App\Models\Concerns\HasOwner;
use Database\Factories\ExamNoticeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $owner_id
 * @property string $name
 * @property string $exam_board
 * @property Carbon|null $exam_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'exam_board', 'exam_date'])]
class ExamNotice extends Model
{
    /** @use HasFactory<ExamNoticeFactory> */
    use HasFactory, HasOwner;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'exam_date' => 'date',
        ];
    }

    /**
     * @return HasMany<ExamSubject, $this>
     */
    public function examSubjects(): HasMany
    {
        return $this->hasMany(ExamSubject::class)->orderBy('order');
    }
}
