<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Exam;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class ReportTablesLayoutTest extends TestCase
{
    public function test_report_table_views_show_application_id_under_photo_without_a_separate_app_id_column(): void
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
            ['reports.choice-list-wise-applicants', []],
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

        $html = view('reports.viva-selected-list', [
            'exam' => new Exam(['name' => 'Layout Test Exam']),
            'applications' => collect([$application]),
            'generatedAt' => CarbonImmutable::parse('2026-05-06 10:00:00'),
        ])->render();

        $this->assertStringContainsString('Viva Sheet', $html);
        $this->assertStringContainsString('>Name<', $html);
        $this->assertStringContainsString('Years of Exp.', $html);
        $this->assertStringContainsString('Place of Work', $html);
        $this->assertStringContainsString('Total Point', $html);
        $this->assertStringContainsString('Invigilator Name', $html);
        $this->assertStringContainsString('Invigilator Signature', $html);
        $this->assertStringContainsString('viva-box', $html);
        $this->assertStringContainsString('Written Marks', $html);
        $this->assertStringContainsString('Viva Voce Marks', $html);
        $this->assertStringContainsString('20260001', $html);
        $this->assertStringContainsString('72.50', $html);
        $this->assertStringContainsString('70.00', $html);
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

        $html = view('reports.viva-selected-list', [
            'exam' => new Exam(['name' => 'Layout Test Exam']),
            'applications' => collect([$application]),
            'generatedAt' => CarbonImmutable::parse('2026-05-06 10:00:00'),
        ])->render();

        $this->assertStringNotContainsString('N/A', $html);
        $this->assertStringNotContainsString('>—<', $html);
        $this->assertMatchesRegularExpression('/<td class="col-exp">\s*<\/td>/', $html);
        $this->assertMatchesRegularExpression('/<td class="col-mark">\s*<\/td>/', $html);
    }
}



