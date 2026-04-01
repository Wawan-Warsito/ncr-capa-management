<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role_name' => $this->faker->unique()->word,
            'display_name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence,
            'level' => $this->faker->numberBetween(1, 5),
            'is_active' => true,
        ];
    }
}
