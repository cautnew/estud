<?php

namespace App\Policies\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Reusable ownership-based authorization for Policies covering models that
 * use the `App\Models\Concerns\HasOwner` trait (i.e. models with an
 * `owner_id` column).
 *
 * Admin bypass is handled globally via `Gate::before` in AppServiceProvider,
 * so this trait only needs to answer "does $user own $model" — it must not
 * duplicate role checks.
 *
 * Usage: `use AuthorizesOwnedResource;` in a concrete Policy class. Override
 * `viewAny()`/`create()` yourself, since those abilities have no model
 * instance to check ownership against.
 */
trait AuthorizesOwnedResource
{
    public function view(User $user, Model $model): bool
    {
        return $this->isOwnedBy($user, $model);
    }

    public function update(User $user, Model $model): bool
    {
        return $this->isOwnedBy($user, $model);
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->isOwnedBy($user, $model);
    }

    protected function isOwnedBy(User $user, Model $model): bool
    {
        return $model->getAttribute('owner_id') === $user->id;
    }
}
