<?php

namespace Tests\Feature\Admin;

use App\Models\Application;
use App\Models\Category;
use App\Models\Exam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApplicationEditPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_page_shows_address_names_instead_of_raw_ids(): void
    {
        $admin = User::factory()->create();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->assignRole('admin');

        $exam = Exam::factory()->create(['status' => 'active']);
        $presentDistrict = Category::factory()->create([
            'type' => 'district',
            'name' => 'Bagerhat',
        ]);
        $presentUpazila = Category::factory()->create([
            'type' => 'upazila',
            'name' => 'Bagerhat Sadar',
            'parent_id' => $presentDistrict->id,
        ]);
        $permanentDistrict = Category::factory()->create([
            'type' => 'district',
            'name' => 'Bandarban',
        ]);
        $permanentUpazila = Category::factory()->create([
            'type' => 'upazila',
            'name' => 'Alikadam',
            'parent_id' => $permanentDistrict->id,
        ]);

        $application = Application::factory()->create([
            'exam_id' => $exam->id,
            'applicant_nid' => '1234567890123',
            'additional_info' => [
                'source' => 'homepage_stepper_form',
                'uploads' => [],
                'personal' => [
                    'gender' => 'Male',
                    'father_name' => 'Test Father',
                    'mother_name' => 'Test Mother',
                    'date_of_birth' => '1998-04-15',
                    'age_as_of_reference' => '28 Years, 1 Months',
                ],
                'education' => [
                    'ssc' => [
                        'examination' => 'SSC',
                        'education_board' => 'Dhaka',
                        'group' => 'Science',
                        'result_type' => 'numeric',
                        'result' => '5.00',
                        'result_scale' => '5.00',
                        'passing_year' => '2014',
                        'institution_name' => 'Test SSC School',
                    ],
                    'hsc' => [
                        'examination' => 'HSC',
                        'education_board' => 'Dhaka',
                        'group' => 'Science',
                        'result_type' => 'numeric',
                        'result' => '5.00',
                        'result_scale' => '5.00',
                        'passing_year' => '2016',
                        'institution_name' => 'Test HSC College',
                    ],
                    'graduation' => [
                        'examination' => 'B.Sc Engineering',
                        'subject' => 'Computer Science',
                        'institution' => 'Test University',
                        'result_type' => 'numeric',
                        'result' => '3.80',
                        'result_scale' => '4.00',
                        'passing_year' => '2020',
                        'course_duration_years' => '4',
                    ],
                    'masters' => [
                        'subject' => 'Computer Science',
                        'institution' => 'Test University',
                        'result_type' => 'numeric',
                        'result' => '3.70',
                        'result_scale' => '4.00',
                        'passing_year' => '2022',
                        'course_duration_years' => '2',
                    ],
                ],
                'job_experience' => [],
                'course_preferences' => [],
                'present_address' => [
                    'district_id' => $presentDistrict->id,
                    'district_name' => $presentDistrict->name,
                    'upazila_id' => $presentUpazila->id,
                    'upazila_name' => $presentUpazila->name,
                    'post_office' => 'GPO',
                    'post_code' => '1000',
                    'address_line' => 'Road 1, House 10',
                ],
                'permanent_address' => [
                    'district_id' => $permanentDistrict->id,
                    'district_name' => $permanentDistrict->name,
                    'upazila_id' => $permanentUpazila->id,
                    'upazila_name' => $permanentUpazila->name,
                    'post_office' => 'GPO',
                    'post_code' => '1000',
                    'address_line' => 'Road 2, House 20',
                ],
            ],
        ]);

        $response = $this->actingAs($admin)->get(route('admin.applications.edit', $application));

        $response->assertOk();
        $response->assertSee('1234567890123', false);
        $response->assertSee('Bagerhat', false);
        $response->assertSee('Bagerhat Sadar', false);
        $response->assertSee('Bandarban', false);
        $response->assertSee('Alikadam', false);
        $response->assertSee('B.Sc Engineering', false);
        $response->assertSee('Test University', false);
        $response->assertDontSee('District ID');
        $response->assertDontSee('Upazila ID');
    }
}
