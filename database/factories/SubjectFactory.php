<?php

namespace Database\Factories;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => null,
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->optional()->paragraph(),
        ];
    }

    /**
     * Indicate that the subject belongs to a specific user instead of being global.
     */
    public function ownedBy(?User $user = null): static
    {
        return $this->state(fn (array $attributes) => [
            'owner_id' => $user !== null ? $user->id : User::factory(),
        ]);
    }
}
