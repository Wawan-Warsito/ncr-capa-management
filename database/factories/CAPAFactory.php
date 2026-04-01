<?php

namespace Database\Factories;

use App\Models\CAPA;
use App\Models\NCR;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CAPA>
 */
class CAPAFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'capa_number' => 'CAPA-' . $this->faker->unique()->numberBetween(1000, 9999),
            'ncr_id' => NCR::factory(),
            'rca_method' => $this->faker->randomElement(['5 Whys', 'Fishbone', 'FMEA']),
            'root_cause_summary' => $this->faker->paragraph,
            'why_1' => $this->faker->sentence,
            'why_2' => $this->faker->sentence,
            'why_3' => $this->faker->sentence,
            'why_4' => $this->faker->sentence,
            'why_5' => $this->faker->sentence,
            'fishbone_people' => $this->faker->sentence,
            'fishbone_process' => $this->faker->sentence,
            'fishbone_material' => $this->faker->sentence,
            'fishbone_equipment' => $this->faker->sentence,
            'fishbone_environment' => $this->faker->sentence,
            'fishbone_measurement' => $this->faker->sentence,
            'corrective_action_plan' => $this->faker->paragraph,
            'preventive_action_plan' => $this->faker->paragraph,
            'expected_outcome' => $this->faker->sentence,
            'assigned_pic_id' => User::factory(),
            'assigned_by_user_id' => User::factory(),
            'assigned_at' => now(),
            'target_completion_date' => now()->addDays(7),
            'actual_completion_date' => null,
            'progress_percentage' => 0,
            'current_status' => 'Draft',
            'effectiveness_verified' => false,
            'verified_by_user_id' => null,
            'verified_at' => null,
            'verification_method' => null,
            'verification_results' => null,
            'monitoring_start_date' => null,
            'monitoring_end_date' => null,
            'monitoring_notes' => null,
            'closed_by_user_id' => null,
            'closed_at' => null,
            'closure_remarks' => null,
        ];
    }
}
