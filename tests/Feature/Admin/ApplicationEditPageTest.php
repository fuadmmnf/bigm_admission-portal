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

    public function test_edit_update_persists_ssc_and_hsc_institution_names(): void
    {
        $admin = User::factory()->create();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->assignRole('admin');

        $exam = Exam::factory()->create(['status' => 'active']);
        $presentDistrict = Category::factory()->create(['type' => 'district', 'name' => 'Bagerhat']);
        $presentUpazila = Category::factory()->create([
            'type' => 'upazila',
            'name' => 'Bagerhat Sadar',
            'parent_id' => $presentDistrict->id,
        ]);
        $permanentDistrict = Category::factory()->create(['type' => 'district', 'name' => 'Bandarban']);
        $permanentUpazila = Category::factory()->create([
            'type' => 'upazila',
            'name' => 'Alikadam',
            'parent_id' => $permanentDistrict->id,
        ]);

        $application = Application::factory()->create([
            'exam_id' => $exam->id,
            'applicant_nid' => '1111111111111',
            'additional_info' => [
                'personal' => [
                    'father_name' => 'Old Father',
                    'mother_name' => 'Old Mother',
                    'date_of_birth' => '1998-01-01',
                ],
                'education' => [],
                'present_address' => [],
                'permanent_address' => [],
                'course_preferences' => [],
            ],
        ]);

        $programs = array_values(config('applicant_form.programs', ['HRM', 'GPP', 'IER', 'PM', 'PSCM', 'PPFM']));

        $response = $this->actingAs($admin)->patch(route('admin.applications.update', $application), [
            'applicant_name' => 'Updated Applicant',
            'father_name' => 'Updated Father',
            'mother_name' => 'Updated Mother',
            'date_of_birth' => '1998-04-15',
            'gender' => 'Male',
            'national_id_number' => '1234567890123',
            'mobile_number_local' => '1712345678',
            'email' => 'updated@example.com',
            'present_address' => [
                'district_id' => $presentDistrict->id,
                'upazila_id' => $presentUpazila->id,
                'post_office' => 'GPO',
                'post_code' => '1000',
                'address_line' => 'Road 1, House 10',
            ],
            'permanent_address' => [
                'district_id' => $permanentDistrict->id,
                'upazila_id' => $permanentUpazila->id,
                'post_office' => 'GPO',
                'post_code' => '1000',
                'address_line' => 'Road 2, House 20',
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
                    'institution_name' => 'Updated SSC Institution',
                ],
                'hsc' => [
                    'examination' => 'HSC',
                    'education_board' => 'Dhaka',
                    'group' => 'Science',
                    'result_type' => 'numeric',
                    'result' => '5.00',
                    'result_scale' => '5.00',
                    'passing_year' => '2016',
                    'institution_name' => 'Updated HSC Institution',
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
                'mphil_phd' => [
                    'subject' => null,
                    'institution' => null,
                    'degree_completion' => 'degree_awarded',
                    'completion_year' => null,
                ],
            ],
            'job_experience' => [
                'total_years' => '3.5',
                'current' => [
                    'job_category' => 'BCS Cadre Service',
                    'organization_name' => 'Dev Company Ltd.',
                    'designation' => 'Software Engineer',
                    'address' => 'Dhaka',
                    'starting_date' => '2022-01-01',
                ],
                'previous' => [
                    'job_category' => null,
                    'organization_name' => null,
                    'designation' => null,
                    'address' => null,
                    'starting_date' => null,
                    'ending_date' => null,
                ],
            ],
            'course_preferences' => [
                'first_choice' => $programs[0],
                'second_choice' => $programs[1],
                'third_choice' => $programs[2],
                'fourth_choice' => $programs[3],
                'fifth_choice' => $programs[4],
                'sixth_choice' => $programs[5],
            ],
        ]);

        $response->assertRedirect(route('admin.applications.show', $application));

        $application->refresh();
        $this->assertSame('Updated SSC Institution', data_get($application->additional_info, 'education.ssc.institution_name'));
        $this->assertSame('Updated HSC Institution', data_get($application->additional_info, 'education.hsc.institution_name'));
    }
}
