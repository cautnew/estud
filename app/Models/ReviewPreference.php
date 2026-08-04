<?php

namespace App\Models;

use App\Models\Concerns\HasOwner;
use Database\Factories\ReviewPreferenceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $owner_id
 * @property array<int, int> $intervals
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['intervals'])]
class ReviewPreference extends Model
{
    /** @use HasFactory<ReviewPreferenceFactory> */
    use HasFactory, HasOwner;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'intervals' => 'array',
        ];
    }
}
