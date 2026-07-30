<?php

namespace App\Models;

use App\Models\Concerns\HasOwner;
use Database\Factories\SubjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $owner_id
 * @property string $name
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'description'])]
class Subject extends Model
{
    /** @use HasFactory<SubjectFactory> */
    use HasFactory, HasOwner;

    /**
     * @return HasMany<Topic, $this>
     */
    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class);
    }

    /**
     * Limit the query to subjects visible to the given user: global
     * subjects (no owner) plus the user's own subjects.
     *
     * @param  Builder<Subject>  $query
     * @return Builder<Subject>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where(
            fn (Builder $query) => $query->whereNull('owner_id')->orWhere('owner_id', $user->id)
        );
    }
}
