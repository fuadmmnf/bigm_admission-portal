<?php

namespace Tests\Feature\Payment;

use App\Models\Application;
use App\Models\Category;
use App\Models\Exam;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentGatewayIntegrationRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_submission_redirects_to_payment_and_keeps_selection_stage_empty(): void
    {
        Storage::fake('public');

        $exam = Exam::factory()->create([
            'status' => 'active',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        [$district, $upazila] = $this->createLocationCategories();

        $response = $this->post(route('applications.store', $exam), $this->validApplicationPayload($district, $upazila));

        $application = Application::query()->latest('id')->firstOrFail();

        $response->assertRedirect(route('payment.initiate', $application));
        $this->assertSame('submitted', $application->status);
        $this->assertNull($application->selection_stage);
    }

    public function test_payment_initiation_does_not_skip_payment_or_auto_select_viva_stage(): void
    {
        config()->set('sslcommerz.sandbox', true);
        config()->set('sslcommerz.sandbox_routes', [
            'success' => '/payment/success',
            'failed' => '/payment/failed',
            'cancel' => '/payment/cancel',
            'ipn' => '/payment/ipn',
        ]);
        config()->set('sslcommerz.default_amount', 10.0);

        Http::fake([
            'sandbox.sslcommerz.com/*' => Http::response([
                'status' => 'SUCCESS',
                'GatewayPageURL' => 'https://sandbox.sslcommerz.com/pay/regression-test-session',
            ], 200),
        ]);

        $application = Application::factory()->create([
            'status' => 'submitted',
            'selection_stage' => null,
            'transaction_id' => null,
            'payment_amount' => null,
        ]);

        $response = $this->get(route('payment.initiate', $application));

        $response->assertRedirect('https://sandbox.sslcommerz.com/pay/regression-test-session');
        $response->assertSessionHas('active_payment_application_ulid', $application->ulid);

        $application->refresh();
        $this->assertSame('pending', $application->status);
        $this->assertNotNull($application->transaction_id);
        $this->assertSame(10.0, (float) $application->payment_amount);
        $this->assertNull($application->selection_stage);
    }

    public function test_success_callback_marks_paid_and_sets_paid_stage_not_viva_selected(): void
    {
        config()->set('sslcommerz.sandbox', true);

        Http::fake([
            'sandbox.sslcommerz.com/*' => Http::response([
                'status' => 'VALID',
                'tran_id' => 'TXN-REGRESSION-PAID-001',
                'amount' => '10.00',
                'card_type' => 'VISA',
            ], 200),
        ]);

        $application = Application::factory()->create([
            'status' => 'pending',
            'selection_stage' => null,
            'transaction_id' => 'TXN-REGRESSION-PAID-001',
            'payment_amount' => 10.00,
        ]);

        $response = $this->post(route('payment.success'), [
            'val_id' => 'VAL-REG-001',
            'tran_id' => 'TXN-REGRESSION-PAID-001',
            'status' => 'VALID',
        ]);

        $response->assertOk();
        $response->assertSee('Payment Successful');

        $application->refresh();
        $this->assertSame('paid', $application->status);
        $this->assertSame(Application::STAGE_PAID, $application->selection_stage);
        $this->assertNotSame(Application::STAGE_VIVA_SELECTED, $application->selection_stage);
    }

    /**
     * @return array{0: Category, 1: Category}
     */
    private function createLocationCategories(): array
    {
        $district = Category::factory()->create([
            'type' => 'district',
            'name' => 'Dhaka',
        ]);

        $upazila = Category::factory()->create([
            'type' => 'upazila',
            'name' => 'Dhamrai',
            'parent_id' => $district->id,
        ]);

        return [$district, $upazila];
    }

    /**
     * @return array<string, mixed>
     */
    private function validApplicationPayload(Category $district, Category $upazila): array
    {
        return [
            'applicant_name' => 'Regression Applicant',
            'applicant_photo' => UploadedFile::fake()->image('photo.jpg', 300, 300),
            'father_name' => 'Father Name',
            'mother_name' => 'Mother Name',
            'date_of_birth' => '1995-02-10',
            'gender' => 'Male',
            'national_id_number' => '1995001122334',
            'mobile_number' => '01711111111',
            'email' => 'regression@example.test',
            'signature' => UploadedFile::fake()->image('signature.png', 300, 80),

            'present_address' => [
                'district_id' => $district->id,
                'upazila_id' => $upazila->id,
                'post_office' => 'Dhamrai Sadar',
                'post_code' => '1350',
                'address_line' => 'House 1, Road 2',
            ],
            'permanent_address' => [
                'district_id' => $district->id,
                'upazila_id' => $upazila->id,
                'post_office' => 'Dhamrai Sadar',
                'post_code' => '1350',
                'address_line' => 'Village Example',
            ],

            'education' => [
                'ssc' => [
                    'examination' => 'SSC',
                    'education_board' => 'Dhaka',
                    'result' => '5.00',
                    'result_scale' => 'GPA',
                    'group' => 'Science',
                    'passing_year' => 2010,
                ],
                'hsc' => [
                    'examination' => 'HSC',
                    'education_board' => 'Dhaka',
                    'result' => '5.00',
                    'result_scale' => 'GPA',
                    'group' => 'Science',
                    'passing_year' => 2012,
                ],
                'graduation' => [
                    'examination' => 'Honors',
                    'subject' => 'Computer Science',
                    'institution' => 'Dhaka University',
                    'result' => '3.75',
                    'result_scale' => 'CGPA',
                    'passing_year' => 2016,
                    'course_duration_years' => 4,
                ],
            ],
            'education_documents' => [
                'ssc' => [
                    'marksheet' => UploadedFile::fake()->create('ssc-marksheet.pdf', 100, 'application/pdf'),
                    'certificate' => UploadedFile::fake()->create('ssc-certificate.pdf', 100, 'application/pdf'),
                ],
                'hsc' => [
                    'marksheet' => UploadedFile::fake()->create('hsc-marksheet.pdf', 100, 'application/pdf'),
                    'certificate' => UploadedFile::fake()->create('hsc-certificate.pdf', 100, 'application/pdf'),
                ],
                'graduation' => [
                    'marksheet' => UploadedFile::fake()->create('grad-marksheet.pdf', 100, 'application/pdf'),
                    'certificate' => UploadedFile::fake()->create('grad-certificate.pdf', 100, 'application/pdf'),
                ],
            ],

            'job_experience' => [
                'total_years' => 2,
                'current' => [
                    'job_category' => 'Private Organization',
                    'organization_name' => 'Example Corp',
                    'designation' => 'Analyst',
                    'address' => 'Dhaka',
                    'starting_date' => '2022-01-01',
                ],
                'previous' => [
                    'job_category' => 'NGO / CSO / Development Partner Organization',
                    'organization_name' => 'Sample NGO',
                    'designation' => 'Officer',
                    'address' => 'Dhaka',
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
        ];
    }
}

