<?php

namespace Database\Factories;

use App\Models\DefectCategory;
use App\Models\Department;
use App\Models\DispositionMethod;
use App\Models\SeverityLevel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NCR>
 */
class NCRFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ncr_number' => $this->faker->unique()->bothify('NCR-2026-##-####'),
            'order_number' => $this->faker->bothify('ORD-####'),
            'project_name' => $this->faker->word,
            'customer_name' => $this->faker->company,
            'product_description' => $this->faker->sentence,
            'drawing_number' => $this->faker->bothify('DWG-####'),
            'material_specification' => $this->faker->word,
            'date_found' => $this->faker->date(),
            'location_found' => $this->faker->word,
            'quantity_affected' => $this->faker->numberBetween(1, 100),
            
            'finder_dept_id' => Department::factory(),
            'receiver_dept_id' => Department::factory(),
            'created_by_user_id' => User::factory(),
            
            'defect_category_id' => DefectCategory::factory(),
            'defect_description' => $this->faker->paragraph,
            'defect_location' => $this->faker->word,
            
            'severity_level_id' => SeverityLevel::factory(),
            
            // Optional fields, can be null by default or set via states
            'disposition_method_id' => null,
            'status' => 'Draft',
        ];
    }

    public function open()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'Submitted',
                'submitted_at' => now(),
            ];
        });
    }

    public function closed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'Closed',
                'submitted_at' => now()->subDays(5),
                'closed_at' => now(),
                'closed_by_user_id' => User::factory(),
            ];
        });
    }
}
