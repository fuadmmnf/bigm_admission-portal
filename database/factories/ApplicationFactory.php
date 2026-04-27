<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\Exam;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Application>
 */
class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    public function definition(): array
    {
        $status = fake()->randomElement(['draft', 'submitted', 'approved', 'rejected', 'pending', 'paid', 'failed', 'cancelled']);

        $selectionStage = null;
        if ($status === 'paid') {
            $selectionStage = fake()->randomElement([
                Application::STAGE_PAID,
                Application::STAGE_VIVA_SELECTED,
                Application::STAGE_PROGRAM_SELECTED,
            ]);
        }

        return [
            'exam_id' => Exam::factory(),
            'applicant_name' => fake()->name(),
            'applicant_email' => fake()->unique()->safeEmail(),
            'applicant_phone' => fake()->phoneNumber(),
            'applicant_id_number' => fake()->numerify('###########'),
            'status' => $status,
            'selection_stage' => $selectionStage,
            'transaction_id' => null,
            'payment_amount' => fake()->randomFloat(2, 100, 2000),
            'payment_method' => null,
            'payment_response' => null,
            'additional_info' => null,
        ];
    }
}
