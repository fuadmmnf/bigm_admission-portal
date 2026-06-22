<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Exam;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class ReportTablesLayoutTest extends TestCase
{
    public function test_report_table_views_keep_application_id_under_photo_without_a_separate_app_id_column(): void
    {
        $application = (object) [
            'ulid' => '01ARZ3NDEKTSV4RRFFQ69G5FAV',
            'application_id' => '20260001',
            'applicant_name' => 'Report Candidate',
            'applicant_phone' => '+8801555555555',
            'applicant_email' => 'candidate@example.test',
            'gender' => 'male',
            'written_exam_marks' => 72.5,
            'viva_exam_marks' => 70,
            'selection_stage' => 'program_selected',
            'photo_data_uri' => null,
            'selectedCategory' => (object) ['name' => 'MBA'],
            'additional_info' => [
                'job_experience' => [
                    'total_years' => 4,
                    'current' => [
                        'job_category' => 'Government',
                        'organization_name' => 'BIGM',
                        'designation' => 'Officer',
                    ],
                ],
                'course_preferences' => [
                    'first_choice' => 'MBA',
                    'second_choice' => 'MPA',
                    'third_choice' => 'MDS',
                    'fourth_choice' => 'MABS',
                    'fifth_choice' => 'MSS',
                    'sixth_choice' => 'LLM',
                ],
            ],
        ];

        $baseData = [
            'exam' => new Exam(['name' => 'Layout Test Exam']),
            'applications' => collect([$application]),
            'generatedAt' => CarbonImmutable::parse('2026-05-06 10:00:00'),
        ];

        $scenarios = [
            ['reports.gender-wise-applicants', ['genderFilter' => null]],
            ['reports.employer-wise-applicants', ['employerFilter' => null]],
            ['reports.enrolled-students', []],
            [
                'reports.choice-list-by-subject',
                [
                    'subject' => 'MBA',
                    'totalCount' => 1,
                    'byChoice' => [
                        'first_choice' => collect([$application]),
                        'second_choice' => collect(),
                        'third_choice' => collect(),
                        'fourth_choice' => collect(),
                        'fifth_choice' => collect(),
                        'sixth_choice' => collect(),
                    ],
                ],
            ],
            [
                'reports.program-selected-by-code',
                ['programCategory' => new Category(['name' => 'MBA', 'additional_info' => ['code' => 'MBA-01']])],
            ],
        ];

        foreach ($scenarios as [$view, $extraData]) {
            $html = view($view, array_merge($baseData, $extraData))->render();

            $this->assertStringContainsString('Photo / App. ID', $html);
            $this->assertStringContainsString('photo-app-id', $html);
            $this->assertStringContainsString('20260001', $html);
            $this->assertSame(0, preg_match('/<th[^>]*>\s*App\.\s*ID\s*<\/th>/i', $html));
        }
    }

    public function test_choice_list_report_uses_a_dedicated_application_id_column(): void
    {
        $application = (object) [
            'ulid' => '01ARZ3NDEKTSV4RRFFQ69G5FAV',
            'application_id' => '20260001',
            'applicant_name' => 'Report Candidate',
            'written_exam_marks' => 72.5,
            'viva_exam_marks' => 70,
            'photo_data_uri' => null,
            'additional_info' => [
                'course_preferences' => [
                    'first_choice' => 'MBA',
                    'second_choice' => 'MPA',
                    'third_choice' => 'MDS',
                    'fourth_choice' => 'MABS',
                    'fifth_choice' => 'MSS',
                    'sixth_choice' => 'LLM',
                ],
            ],
        ];

        $html = view('reports.choice-list-wise-applicants', [
            'exam' => new Exam(['name' => 'Layout Test Exam']),
            'applications' => collect([$application]),
            'generatedAt' => CarbonImmutable::parse('2026-05-06 10:00:00'),
        ])->render();

        $this->assertStringContainsString('<th class="col-app-id">Application ID</th>', $html);
        $this->assertStringContainsString('<th class="col-photo">Photo</th>', $html);
        $this->assertStringContainsString('<td class="col-app-id">20260001</td>', $html);
        $this->assertStringNotContainsString('<div class="photo-app-id">', $html);
        $this->assertStringNotContainsString('Photo / App. ID', $html);
    }

    public function test_attendance_sheet_shows_two_invigilator_blocks_in_footer(): void
    {
        $application = (object) [
            'ulid' => '01ARZ3NDEKTSV4RRFFQ69G5FAC',
            'application_id' => '20260004',
            'applicant_name' => 'Attendance Candidate',
            'photo_data_uri' => null,
            'additional_info' => [],
        ];

        $html = view('reports.attendance-list', [
            'exam' => new Exam(['name' => 'Layout Test Exam']),
            'applications' => collect([$application]),
            'generatedAt' => CarbonImmutable::parse('2026-05-06 10:00:00'),
        ])->render();

        $this->assertStringContainsString('Invigilator 1 Name', $html);
        $this->assertStringContainsString('Invigilator 1 Signature', $html);
        $this->assertStringContainsString('Invigilator 2 Name', $html);
        $this->assertStringContainsString('Invigilator 2 Signature', $html);
        $this->assertStringContainsString('<div class="att-footer-block">', $html);
        $this->assertStringContainsString('<div class="att-footer-block right">', $html);
        $this->assertSame(4, substr_count($html, 'class="att-footer-box"'));
        $this->assertSame(4, substr_count($html, 'class="att-footer-label"'));
    }

    public function test_viva_sheet_view_shows_requested_columns_and_marks(): void
    {
        $application = (object) [
            'ulid' => '01ARZ3NDEKTSV4RRFFQ69G5FAV',
            'application_id' => '20260001',
            'applicant_name' => 'Viva Candidate',
            'written_exam_marks' => 72.5,
            'viva_exam_marks' => 70,
            'additional_info' => [
                'job_experience' => [
                    'total_years' => 4,
                    'current' => [
                        'organization_name' => 'BIGM',
                        'designation' => 'Officer',
                    ],
                ],
                'education' => [
                    'ssc' => ['result' => 4.5, 'result_scale' => 5],
                    'hsc' => ['result' => 4.7, 'result_scale' => 5],
                    'graduation' => ['result' => 3.2, 'result_scale' => 4],
                    'masters' => ['result' => 3.6, 'result_scale' => 4],
                ],
            ],
        ];

        $html = view('reports.viva-sheet', [
            'exam' => new Exam(['name' => 'Layout Test Exam']),
            'applications' => collect([$application]),
            'generatedAt' => CarbonImmutable::parse('2026-05-06 10:00:00'),
        ])->render();

        $this->assertStringContainsString('Viva Sheet', $html);
        $this->assertStringContainsString('>Name<', $html);
        $this->assertStringContainsString('>Total Exp.<', $html);
        $this->assertStringContainsString('>Workplace<', $html);
        $this->assertStringContainsString('>Point<', $html);
        $this->assertStringContainsString('Invigilator Name', $html);
        $this->assertStringContainsString('Signature:', $html);
        $this->assertStringContainsString('viva-box', $html);
        $this->assertStringContainsString('>Written<', $html);
        $this->assertStringContainsString('>Viva<', $html);
        $this->assertStringContainsString('20260001', $html);
        $this->assertStringContainsString('72.50', $html);
        $this->assertStringContainsString('70.00', $html);
        $this->assertMatchesRegularExpression('/<td class="col-edu">\s*3\s*<\/td>/', $html);
        $this->assertMatchesRegularExpression('/<td class="col-edu">\s*2\s*<\/td>/', $html);
        $this->assertMatchesRegularExpression('/<td class="col-point">\s*11\s*<\/td>/', $html);
        $this->assertStringNotContainsString('4.5', $html);
        $this->assertStringNotContainsString('4.7', $html);
        $this->assertStringNotContainsString('3.2', $html);
        $this->assertStringNotContainsString('3.6', $html);
    }

    public function test_viva_sheet_keeps_missing_optional_fields_blank(): void
    {
        $application = (object) [
            'ulid' => '01ARZ3NDEKTSV4RRFFQ69G5FAA',
            'application_id' => '20260002',
            'applicant_name' => 'Blank Candidate',
            'written_exam_marks' => null,
            'viva_exam_marks' => null,
            'additional_info' => [],
        ];

        $html = view('reports.viva-sheet', [
            'exam' => new Exam(['name' => 'Layout Test Exam']),
            'applications' => collect([$application]),
            'generatedAt' => CarbonImmutable::parse('2026-05-06 10:00:00'),
        ])->render();

        $this->assertStringNotContainsString('N/A', $html);
        $this->assertStringNotContainsString('>—<', $html);
        $this->assertMatchesRegularExpression('/<td class="col-exp">\s*<\/td>/', $html);
        $this->assertMatchesRegularExpression('/<td class="col-mark">\s*<\/td>/', $html);
        $this->assertMatchesRegularExpression('/<td class="col-point">\s*0\s*<\/td>/', $html);
    }

    public function test_viva_sheet_points_rules_support_division_and_thresholds(): void
    {
        $application = (object) [
            'ulid' => '01ARZ3NDEKTSV4RRFFQ69G5FAB',
            'application_id' => '20260003',
            'applicant_name' => 'Rules Candidate',
            'written_exam_marks' => null,
            'viva_exam_marks' => null,
            'additional_info' => [
                'education' => [
                    'ssc' => ['result' => '1st Division'],
                    'hsc' => ['result' => 3.99],
                    'graduation' => ['result' => 3.00],
                    'masters' => ['result' => 'Second Division'],
                ],
            ],
        ];

        $html = view('reports.viva-sheet', [
            'exam' => new Exam(['name' => 'Layout Test Exam']),
            'applications' => collect([$application]),
            'generatedAt' => CarbonImmutable::parse('2026-05-06 10:00:00'),
        ])->render();

        // SSC=3 (1st division), HSC=0 (CGPA<=4), Graduation=3 (>=3), Masters present=2 => total 8
        $this->assertMatchesRegularExpression('/<td class="col-point">\s*8\s*<\/td>/', $html);
        $this->assertStringNotContainsString('1st Division', $html);
        $this->assertStringNotContainsString('3.99', $html);
        $this->assertStringNotContainsString('Second Division', $html);
    }
}



