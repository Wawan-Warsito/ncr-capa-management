<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'department_name' => $this->faker->company,
            'department_code' => $this->faker->unique()->bothify('DEPT-##'),
            'description' => $this->faker->sentence,
            'is_active' => true,
        ];
    }
}
