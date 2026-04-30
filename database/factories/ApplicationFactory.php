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

    public static function fakeAdditionalInfo(string $gender): array
    {
        $boards = ['Dhaka', 'Chattogram', 'Rajshahi', 'Sylhet', 'Barishal', 'Khulna', 'Comilla', 'Mymensingh'];
        $resultScales = ['GPA', 'CGPA', 'Division'];
        $programs = ['Computer Science', 'Business Administration', 'Public Health', 'Development Studies', 'Finance', 'Economics'];
        $designations = ['Software Engineer', 'Analyst', 'Manager', 'Officer', 'Executive', 'Coordinator'];
        $orgs = ['Government Hospital', 'Bangladesh Bank', 'A.C.I. Ltd', 'BRAC', 'Unilever Bangladesh', 'Robi Axiata'];
        $categories = ['Government', 'Private', 'NGO', 'Semi-Government', 'Autonomous'];

        return [
            'source' => 'factory',
            'personal' => [
                'father_name'        => fake()->name('male'),
                'mother_name'        => fake()->name('female'),
                'gender'             => $gender,
                'date_of_birth'      => fake()->date('Y-m-d', '-18 years'),
                'age_as_of_reference'=> fake()->numberBetween(18, 45).' Years, '.fake()->numberBetween(0, 11).' Months',
            ],
            'present_address' => [
                'address_line'  => fake()->streetAddress(),
                'post_office'   => fake()->city(),
                'post_code'     => fake()->numerify('####'),
                'upazila_name'  => fake()->city(),
                'district_name' => fake()->randomElement(['Dhaka', 'Chattogram', 'Rajshahi', 'Sylhet', 'Khulna']),
            ],
            'permanent_address' => [
                'address_line'  => fake()->streetAddress(),
                'post_office'   => fake()->city(),
                'post_code'     => fake()->numerify('####'),
                'upazila_name'  => fake()->city(),
                'district_name' => fake()->randomElement(['Dhaka', 'Chattogram', 'Rajshahi', 'Sylhet', 'Khulna']),
            ],
            'education' => [
                'ssc' => [
                    'examination'    => 'SSC',
                    'education_board'=> fake()->randomElement($boards),
                    'result'         => number_format(fake()->randomFloat(2, 3.0, 5.0), 2),
                    'result_scale'   => 'GPA',
                    'passing_year'   => (string) fake()->numberBetween(2000, 2012),
                ],
                'hsc' => [
                    'examination'    => 'HSC',
                    'education_board'=> fake()->randomElement($boards),
                    'result'         => number_format(fake()->randomFloat(2, 3.0, 5.0), 2),
                    'result_scale'   => 'GPA',
                    'passing_year'   => (string) fake()->numberBetween(2002, 2014),
                ],
                'graduation' => [
                    'examination'    => 'B.Sc.',
                    'subject'        => fake()->randomElement($programs),
                    'institution'    => fake()->randomElement(['Dhaka University', 'BUET', 'KUET', 'RUET', 'NSU']),
                    'result'         => number_format(fake()->randomFloat(2, 2.5, 4.0), 2),
                    'result_scale'   => 'CGPA',
                    'passing_year'   => (string) fake()->numberBetween(2006, 2018),
                ],
                'masters' => [
                    'examination'    => 'M.Sc.',
                    'subject'        => fake()->randomElement($programs),
                    'institution'    => fake()->randomElement(['Dhaka University', 'IBA', 'BRAC University', 'East West University']),
                    'result'         => number_format(fake()->randomFloat(2, 2.5, 4.0), 2),
                    'result_scale'   => 'CGPA',
                    'passing_year'   => (string) fake()->numberBetween(2010, 2022),
                ],
            ],
            'job_experience' => [
                'total_years' => (string) fake()->numberBetween(0, 15),
                'current' => [
                    'designation'       => fake()->randomElement($designations),
                    'organization_name' => fake()->randomElement($orgs),
                    'job_category'      => fake()->randomElement($categories),
                ],
                'previous' => [
                    'designation'       => fake()->randomElement($designations),
                    'organization_name' => fake()->randomElement($orgs),
                ],
            ],
            'course_preferences' => [
                'first_choice'  => fake()->randomElement($programs),
                'second_choice' => fake()->randomElement($programs),
                'third_choice'  => fake()->randomElement($programs),
                'fourth_choice' => fake()->randomElement($programs),
                'fifth_choice'  => fake()->randomElement($programs),
                'sixth_choice'  => fake()->randomElement($programs),
            ],
            'uploads' => [],
        ];
    }

    public function definition(): array
    {
        $status = fake()->randomElement(['draft', 'submitted', 'approved', 'rejected', 'pending', 'paid', 'failed', 'cancelled']);
        $gender = fake()->randomElement(['Male', 'Female', 'Other']);

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
            'gender' => $gender,
            'status' => $status,
            'selection_stage' => $selectionStage,
            'transaction_id' => null,
            'payment_amount' => fake()->randomFloat(2, 100, 2000),
            'payment_method' => null,
            'payment_response' => null,
            'written_exam_marks' => null,
            'viva_exam_marks' => null,
            'selected_category_id' => null,
            'additional_info' => static::fakeAdditionalInfo($gender),
        ];
    }
}
