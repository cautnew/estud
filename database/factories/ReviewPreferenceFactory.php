<?php

namespace Database\Factories;

use App\Models\ReviewPreference;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReviewPreference>
 */
class ReviewPreferenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'intervals' => config('reviews.default_intervals'),
        ];
    }
}
