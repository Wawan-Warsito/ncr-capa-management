<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SeverityLevel>
 */
class SeverityLevelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'level_name' => $this->faker->word,
            'level_code' => $this->faker->unique()->bothify('SEV-##'),
            'priority' => $this->faker->numberBetween(1, 5),
            'color_code' => $this->faker->hexColor,
            'description' => $this->faker->sentence,
            'is_active' => true,
        ];
    }
}
