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
            ['reports.viva-selected-list', []],
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
            ['reports.job-experience-wise-applicants', []],
        ];

        foreach ($scenarios as [$view, $extraData]) {
            $html = view($view, array_merge($baseData, $extraData))->render();

            $this->assertStringContainsString('Photo / App. ID', $html);
            $this->assertStringContainsString('photo-app-id', $html);
            $this->assertStringContainsString('20260001', $html);
            $this->assertSame(0, preg_match('/<th[^>]*>\s*App\.\s*ID\s*<\/th>/i', $html));
        }
    }
}



