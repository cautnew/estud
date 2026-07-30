<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Adds automatic ownership tracking to a model that has an `owner_id`
 * foreign key column referencing `users.id`.
 *
 * Usage:
 *   1. Add `$table->foreignId('owner_id')->constrained('users');` (or nullable,
 *      per the resource's needs) to the model's migration.
 *   2. `use HasOwner;` on the model.
 *
 * Behavior:
 *   - On creation, `owner_id` is auto-filled from `Auth::id()` if not already
 *     set (e.g. when seeding/factories explicitly assign an owner).
 *   - Exposes an `owner()` relation to `App\Models\User`.
 *   - Exposes a local `ownedBy()` scope for filtering by owner.
 */
trait HasOwner
{
    public static function bootHasOwner(): void
    {
        static::creating(function (Model $model): void {
            if (! $model->getAttribute('owner_id') && Auth::check()) {
                $model->setAttribute('owner_id', Auth::id());
            }
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('owner_id', $user->id);
    }
}
