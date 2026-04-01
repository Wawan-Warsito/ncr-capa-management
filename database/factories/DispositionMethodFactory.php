<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DispositionMethod>
 */
class DispositionMethodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'method_name' => $this->faker->word,
            'method_code' => $this->faker->unique()->bothify('DISP-##'),
            'description' => $this->faker->sentence,
            'requires_approval' => $this->faker->boolean,
            'is_active' => true,
        ];
    }
}
