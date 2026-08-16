<?php

namespace App\Models;

use App\Enums\StudyCycleType;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasOwner;
use Database\Factories\StudyCycleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $owner_id
 * @property string $name
 * @property StudyCycleType $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'type'])]
class StudyCycle extends Model
{
    /** @use HasFactory<StudyCycleFactory> */
    use Auditable, HasFactory, HasOwner;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => StudyCycleType::class,
        ];
    }

    /**
     * @return HasMany<StudyCycleItem, $this>
     */
    public function studyCycleItems(): HasMany
    {
        return $this->hasMany(StudyCycleItem::class)->orderBy('order');
    }
}
