<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Category;
use App\Models\Exam;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicationFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_user_can_open_application_form_for_active_exam(): void
    {
        $exam = Exam::factory()->create([
            'status' => 'active',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $response = $this->get(route('applications.create', $exam));

        $response->assertOk();
        $response->assertSee('Admission Application Form');
        $response->assertSee($exam->name);
        $response->assertSee('Read Before You Start Application');
        $response->assertSeeText('Payment Information');
        $response->assertSee('If payment fails or is cancelled, the submitted application will be deleted.');
    }

    public function test_application_form_returns_not_found_before_start_date(): void
    {
        $exam = Exam::factory()->create([
            'status' => 'active',
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(5),
        ]);

        $response = $this->get(route('applications.create', $exam));

        $response->assertNotFound();
    }

    public function test_application_form_returns_not_found_after_end_date(): void
    {
        $exam = Exam::factory()->create([
            'status' => 'active',
            'start_date' => now()->subDays(5),
            'end_date' => now()->subMinute(),
        ]);

        $response = $this->get(route('applications.create', $exam));

        $response->assertNotFound();
    }

    public function test_application_form_returns_not_found_for_non_active_exam(): void
    {
        $exam = Exam::factory()->create([
            'status' => 'draft',
        ]);

        $response = $this->get(route('applications.create', $exam));

        $response->assertNotFound();
    }

    public function test_submitting_stepper_form_creates_application_and_redirects_to_payment(): void
    {
        Storage::fake('public');

        $exam = Exam::factory()->create([
            'status' => 'active',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $presentDistrict = Category::factory()->create([
            'type' => 'district',
            'name' => 'Dhaka',
        ]);
        $presentUpazila = Category::factory()->create([
            'type' => 'upazila',
            'name' => 'Dhanmondi',
            'parent_id' => $presentDistrict->id,
        ]);

        $permanentDistrict = Category::factory()->create([
            'type' => 'district',
            'name' => 'Faridpur',
        ]);
        $permanentUpazila = Category::factory()->create([
            'type' => 'upazila',
            'name' => 'Faridpur Sadar',
            'parent_id' => $permanentDistrict->id,
        ]);

        $payload = [
            'applicant_name' => 'Rahim Uddin',
            'applicant_photo' => UploadedFile::fake()->image('photo.png', 300, 300),
            'father_name' => 'Abdul Karim',
            'mother_name' => 'Sufia Khatun',
            'gender' => 'Male',
            'date_of_birth' => '1997-05-10',
            'age_as_of_reference' => '28 Years, 0 Months',
            'national_id_number' => '19901234567890123',
            'mobile_number_local' => '1710000000',
            'email' => 'rahim@example.com',
            'signature' => UploadedFile::fake()->image('signature.png', 300, 80),

            'present_address' => [
                'district_id' => $presentDistrict->id,
                'upazila_id' => $presentUpazila->id,
                'post_office' => 'New Market',
                'post_code' => '1205',
                'address_line' => 'Road 4, House 11',
            ],
            'permanent_address' => [
                'district_id' => $permanentDistrict->id,
                'upazila_id' => $permanentUpazila->id,
                'post_office' => 'Faridpur',
                'post_code' => '7800',
                'address_line' => 'Village Charpara',
            ],

            'education' => [
                'ssc' => [
                    'examination' => 'SSC',
                    'education_board' => 'Dhaka',
                    'result_type' => 'numeric',
                    'result' => '5.00',
                    'result_scale' => '5.00',
                    'group' => 'Science',
                    'passing_year' => 2013,
                ],
                'hsc' => [
                    'examination' => 'HSC',
                    'education_board' => 'Dhaka',
                    'result_type' => 'numeric',
                    'result' => '5.00',
                    'result_scale' => '5.00',
                    'group' => 'Science',
                    'passing_year' => 2015,
                ],
                'graduation' => [
                    'examination' => 'Honors',
                    'subject' => 'Public Administration',
                    'institution' => 'University of Dhaka',
                    'result_type' => 'numeric',
                    'result' => '3.72',
                    'result_scale' => '4.00',
                    'passing_year' => 2019,
                    'course_duration_years' => 4,
                ],
                'masters' => [
                    'examination' => 'M.S.S',
                    'subject' => 'Public Administration',
                    'institution' => 'University of Dhaka',
                    'result_type' => 'numeric',
                    'result' => '3.80',
                    'result_scale' => '4.00',
                    'passing_year' => 2020,
                    'course_duration_years' => 1,
                ],
                'mphil_phd' => [
                    'subject' => 'Public Administration',
                    'institution' => 'University of Dhaka',
                    'degree_completion' => 'degree_awarded',
                    'completion_year' => 2023,
                ],
            ],
            'education_documents' => [
                'ssc' => ['certificate' => UploadedFile::fake()->create('ssc-certificate.pdf', 300, 'application/pdf')],
                'hsc' => ['certificate' => UploadedFile::fake()->create('hsc-certificate.pdf', 300, 'application/pdf')],
                'graduation' => ['certificate' => UploadedFile::fake()->create('graduation-certificate.pdf', 300, 'application/pdf')],
                'masters' => ['certificate' => UploadedFile::fake()->create('masters-certificate.pdf', 300, 'application/pdf')],
            ],

            'job_experience' => [
                'total_years' => 4,
                'current' => [
                    'job_category' => 'Govt. Non-Cadre (9th grade and above)',
                    'organization_name' => 'Ministry of Education',
                    'designation' => 'Assistant Director',
                    'address' => 'Dhaka Secretariat',
                    'starting_date' => '2022-01-01',
                ],
                'previous' => [
                    'job_category' => 'Private Organization',
                    'organization_name' => 'ABC Consulting',
                    'designation' => 'Executive',
                    'address' => 'Motijheel, Dhaka',
                    'starting_date' => '2020-01-01',
                    'ending_date' => '2021-12-31',
                ],
            ],

            'course_preferences' => [
                'first_choice' => 'HRM',
                'second_choice' => 'GPP',
                'third_choice' => 'IER',
                'fourth_choice' => 'PM',
                'fifth_choice' => 'PSCM',
                'sixth_choice' => 'PPFM',
            ],

            'declaration' => '1',
            'contact_info_confirmation' => '1',
        ];

        $response = $this->post(route('applications.store', $exam), $payload);

        $application = Application::query()->latest('id')->first();

        $this->assertNotNull($application);
        $response->assertRedirect(route('payment.initiate', $application));

        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'exam_id' => $exam->id,
            'applicant_name' => 'Rahim Uddin',
            'applicant_email' => 'rahim@example.com',
            'applicant_phone' => '+8801710000000',
            'gender' => 'Male',
            'status' => 'submitted',
        ]);

        $this->assertSame('homepage_stepper_form', $application->additional_info['source']);
        $this->assertTrue((bool) data_get($application->additional_info, 'confirmations.declaration'));
        $this->assertTrue((bool) data_get($application->additional_info, 'confirmations.contact_info_confirmation'));
        $this->assertSame('Male', data_get($application->additional_info, 'personal.gender'));
        $this->assertSame('Dhaka', $application->additional_info['present_address']['district_name']);
        $this->assertSame('Dhanmondi', $application->additional_info['present_address']['upazila_name']);
        Storage::disk('public')->assertExists($application->additional_info['uploads']['applicant_photo']);
        Storage::disk('public')->assertExists($application->additional_info['uploads']['signature']);
        Storage::disk('public')->assertExists($application->additional_info['uploads']['education_documents']['ssc']['certificate']);
        Storage::disk('public')->assertExists($application->additional_info['uploads']['education_documents']['hsc']['certificate']);
        Storage::disk('public')->assertExists($application->additional_info['uploads']['education_documents']['graduation']['certificate']);

        $this->assertSame('numeric', data_get($application->additional_info, 'education.ssc.result_type'));
        $this->assertSame('numeric', data_get($application->additional_info, 'education.hsc.result_type'));
        $this->assertSame('numeric', data_get($application->additional_info, 'education.graduation.result_type'));
        $this->assertSame('numeric', data_get($application->additional_info, 'education.masters.result_type'));
        $this->assertNull(data_get($application->additional_info, 'education.mphil_phd.result_type'));
    }

    public function test_application_form_accepts_division_style_results_for_all_supported_education_levels(): void
    {
        Storage::fake('public');

        $exam = Exam::factory()->create([
            'status' => 'active',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $district = Category::factory()->create(['type' => 'district']);
        $upazila = Category::factory()->create(['type' => 'upazila', 'parent_id' => $district->id]);

        $payload = [
            'applicant_name' => 'Division Candidate',
            'applicant_photo' => UploadedFile::fake()->image('photo.png', 300, 300),
            'father_name' => 'Father',
            'mother_name' => 'Mother',
            'gender' => 'Male',
            'date_of_birth' => '1998-01-01',
            'age_as_of_reference' => '27 Years, 0 Months',
            'national_id_number' => '1000000000001',
            'mobile_number_local' => '1719999999',
            'email' => 'division@example.test',
            'signature' => UploadedFile::fake()->image('signature.png', 300, 80),
            'present_address' => [
                'district_id' => $district->id,
                'upazila_id' => $upazila->id,
                'post_office' => 'PO',
                'post_code' => '1200',
                'address_line' => 'Address 1',
            ],
            'permanent_address' => [
                'district_id' => $district->id,
                'upazila_id' => $upazila->id,
                'post_office' => 'PO',
                'post_code' => '1200',
                'address_line' => 'Address 2',
            ],
            'education' => [
                'ssc' => [
                    'examination' => 'SSC',
                    'education_board' => 'Dhaka',
                    'result_type' => 'division',
                    'division' => 'First Division',
                    'group' => 'Science',
                    'passing_year' => 2012,
                ],
                'hsc' => [
                    'examination' => 'HSC',
                    'education_board' => 'Dhaka',
                    'result_type' => 'division',
                    'division' => 'Second Division',
                    'group' => 'Science',
                    'passing_year' => 2014,
                ],
                'graduation' => [
                    'examination' => 'Honors',
                    'subject' => 'Economics',
                    'institution' => 'University of Dhaka',
                    'result_type' => 'division',
                    'division' => 'First Division',
                    'passing_year' => 2018,
                    'course_duration_years' => 4,
                ],
                'masters' => [
                    'examination' => 'MSS',
                    'subject' => 'Economics',
                    'institution' => 'University of Dhaka',
                    'result_type' => 'division',
                    'division' => 'Second Division',
                    'passing_year' => 2020,
                    'course_duration_years' => 1,
                ],
                'mphil_phd' => [
                    'subject' => 'Economics',
                    'institution' => 'University of Dhaka',
                    'degree_completion' => 'degree_awarded',
                    'completion_year' => 2022,
                ],
            ],
            'education_documents' => [
                'ssc' => ['certificate' => UploadedFile::fake()->create('ssc-certificate.pdf', 300, 'application/pdf')],
                'hsc' => ['certificate' => UploadedFile::fake()->create('hsc-certificate.pdf', 300, 'application/pdf')],
                'graduation' => ['certificate' => UploadedFile::fake()->create('graduation-certificate.pdf', 300, 'application/pdf')],
            ],
            'job_experience' => ['total_years' => 2],
            'course_preferences' => [
                'first_choice' => 'HRM',
                'second_choice' => 'GPP',
                'third_choice' => 'IER',
                'fourth_choice' => 'PM',
                'fifth_choice' => 'PSCM',
                'sixth_choice' => 'PPFM',
            ],
            'declaration' => '1',
            'contact_info_confirmation' => '1',
        ];

        $response = $this->post(route('applications.store', $exam), $payload);

        $application = Application::query()->latest('id')->first();
        $response->assertRedirect(route('payment.initiate', $application));

        $this->assertSame('division', data_get($application->additional_info, 'education.ssc.result_type'));
        $this->assertSame('First Division', data_get($application->additional_info, 'education.ssc.result'));
        $this->assertSame('Division', data_get($application->additional_info, 'education.ssc.result_scale'));
        $this->assertSame('division', data_get($application->additional_info, 'education.hsc.result_type'));
        $this->assertSame('Second Division', data_get($application->additional_info, 'education.hsc.result'));
        $this->assertSame('division', data_get($application->additional_info, 'education.graduation.result_type'));
        $this->assertSame('First Division', data_get($application->additional_info, 'education.graduation.result'));
        $this->assertSame('division', data_get($application->additional_info, 'education.masters.result_type'));
        $this->assertSame('Second Division', data_get($application->additional_info, 'education.masters.result'));
        $this->assertNull(data_get($application->additional_info, 'education.mphil_phd.result_type'));
        $this->assertNull(data_get($application->additional_info, 'education.mphil_phd.result'));
    }

    public function test_application_form_allows_submission_without_masters_and_defaults_total_work_experience_to_zero(): void
    {
        Storage::fake('public');

        $exam = Exam::factory()->create([
            'status' => 'active',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $district = Category::factory()->create(['type' => 'district']);
        $upazila = Category::factory()->create(['type' => 'upazila', 'parent_id' => $district->id]);

        $payload = $this->validApplicationPayload($district, $upazila, 'nomasters@example.test', '1712222222');
        unset($payload['education']['masters'], $payload['job_experience']['total_years']);

        $response = $this->post(route('applications.store', $exam), $payload);

        $application = Application::query()->latest('id')->first();
        $response->assertRedirect(route('payment.initiate', $application));
        $this->assertNull(data_get($application->additional_info, 'education.masters.result_type'));
        $this->assertSame(0, data_get($application->additional_info, 'job_experience.total_years'));
    }

    public function test_application_form_allows_zero_total_work_experience(): void
    {
        Storage::fake('public');

        $exam = Exam::factory()->create([
            'status' => 'active',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $district = Category::factory()->create(['type' => 'district']);
        $upazila = Category::factory()->create(['type' => 'upazila', 'parent_id' => $district->id]);

        $payload = $this->validApplicationPayload($district, $upazila, 'zeroexp@example.test', '1713333333');
        $payload['job_experience']['total_years'] = 0;

        $response = $this->post(route('applications.store', $exam), $payload);

        $application = Application::query()->latest('id')->first();
        $response->assertRedirect(route('payment.initiate', $application));
        $this->assertSame(0, data_get($application->additional_info, 'job_experience.total_years'));
    }

    private function validApplicationPayload(Category $district, Category $upazila, string $email, string $mobile): array
    {
        return [
            'applicant_name' => 'Validation Candidate',
            'applicant_photo' => UploadedFile::fake()->image('photo.png', 300, 300),
            'father_name' => 'Father',
            'mother_name' => 'Mother',
            'gender' => 'Male',
            'date_of_birth' => '1998-01-01',
            'age_as_of_reference' => '27 Years, 0 Months',
            'national_id_number' => '1000000000002',
            'mobile_number_local' => $mobile,
            'email' => $email,
            'signature' => UploadedFile::fake()->image('signature.png', 300, 80),
            'present_address' => [
                'district_id' => $district->id,
                'upazila_id' => $upazila->id,
                'post_office' => 'PO',
                'post_code' => '1200',
                'address_line' => 'Address 1',
            ],
            'permanent_address' => [
                'district_id' => $district->id,
                'upazila_id' => $upazila->id,
                'post_office' => 'PO',
                'post_code' => '1200',
                'address_line' => 'Address 2',
            ],
            'education' => [
                'ssc' => [
                    'examination' => 'SSC',
                    'education_board' => 'Dhaka',
                    'result_type' => 'numeric',
                    'result' => '5.00',
                    'result_scale' => '5.00',
                    'group' => 'Science',
                    'passing_year' => 2012,
                ],
                'hsc' => [
                    'examination' => 'HSC',
                    'education_board' => 'Dhaka',
                    'result_type' => 'numeric',
                    'result' => '5.00',
                    'result_scale' => '5.00',
                    'group' => 'Science',
                    'passing_year' => 2014,
                ],
                'graduation' => [
                    'examination' => 'Honors',
                    'subject' => 'Economics',
                    'institution' => 'University of Dhaka',
                    'result_type' => 'numeric',
                    'result' => '3.70',
                    'result_scale' => '4.00',
                    'passing_year' => 2018,
                    'course_duration_years' => 4,
                ],
            ],
            'education_documents' => [
                'ssc' => ['certificate' => UploadedFile::fake()->create('ssc-certificate.pdf', 300, 'application/pdf')],
                'hsc' => ['certificate' => UploadedFile::fake()->create('hsc-certificate.pdf', 300, 'application/pdf')],
                'graduation' => ['certificate' => UploadedFile::fake()->create('graduation-certificate.pdf', 300, 'application/pdf')],
            ],
            'job_experience' => [
                'total_years' => 2,
            ],
            'course_preferences' => [
                'first_choice' => 'HRM',
                'second_choice' => 'GPP',
                'third_choice' => 'IER',
                'fourth_choice' => 'PM',
                'fifth_choice' => 'PSCM',
                'sixth_choice' => 'PPFM',
            ],
            'declaration' => '1',
            'contact_info_confirmation' => '1',
        ];
    }
}
