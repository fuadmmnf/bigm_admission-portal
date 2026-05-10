<?php

namespace App\Http\Requests\Applicant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreApplicationRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $mobileLocal = preg_replace('/\D+/', '', (string) $this->input('mobile_number_local', ''));

        if (str_starts_with($mobileLocal, '880')) {
            $mobileLocal = substr($mobileLocal, 3);
        }

        if (str_starts_with($mobileLocal, '0')) {
            $mobileLocal = substr($mobileLocal, 1);
        }

        $this->merge([
            'mobile_number_local' => $mobileLocal,
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $currentYear = (int) now()->format('Y');
        $programs = config('applicant_form.programs', []);
        $genders = config('applicant_form.genders', []);
        $resultTypes = array_keys(config('applicant_form.education_result_types', ['numeric' => 'GPA/CGPA', 'division' => 'Division']));
        $divisions = config('applicant_form.education_divisions', []);
        $certificateMaxKb = (int) config('applicant_uploads.certificate_pdf.max_kb', 5120);

        return [
            'applicant_name' => ['required', 'string', 'max:255'],
            'applicant_photo' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:1024', 'dimensions:width=300,height=300'],
            'father_name' => ['required', 'string', 'max:255'],
            'mother_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'age_as_of_reference' => ['nullable', 'string', 'max:120'],
            'gender' => ['required', 'string', Rule::in($genders)],
            'national_id_number' => ['required', 'string', 'max:120'],
            'mobile_number_local' => ['required', 'regex:/^1\d{9}$/'],
            'email' => ['required', 'email', 'max:255'],
            'signature' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:1024', 'dimensions:width=300,height=80'],

            'present_address.district_id' => ['required', 'integer', Rule::exists('categories', 'id')->where('type', 'district')],
            'present_address.upazila_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query): void {
                    $query
                        ->where('type', 'upazila')
                        ->where('parent_id', $this->integer('present_address.district_id'));
                }),
            ],
            'present_address.post_office' => ['required', 'string', 'max:120'],
            'present_address.post_code' => ['required', 'string', 'max:20'],
            'present_address.address_line' => ['required', 'string', 'max:255'],

            'permanent_address.district_id' => ['required', 'integer', Rule::exists('categories', 'id')->where('type', 'district')],
            'permanent_address.upazila_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query): void {
                    $query
                        ->where('type', 'upazila')
                        ->where('parent_id', $this->integer('permanent_address.district_id'));
                }),
            ],
            'permanent_address.post_office' => ['required', 'string', 'max:120'],
            'permanent_address.post_code' => ['required', 'string', 'max:20'],
            'permanent_address.address_line' => ['required', 'string', 'max:255'],

            'education.ssc.examination' => ['required', 'string', 'max:120'],
            'education.ssc.education_board' => ['required', 'string', 'max:120'],
            'education.ssc.result_type' => ['required', 'string', Rule::in($resultTypes)],
            'education.ssc.result' => ['nullable', 'numeric', 'min:0', 'required_if:education.ssc.result_type,numeric'],
            'education.ssc.result_scale' => ['nullable', 'numeric', 'min:0', 'required_if:education.ssc.result_type,numeric'],
            'education.ssc.division' => ['nullable', 'string', Rule::in($divisions), 'required_if:education.ssc.result_type,division'],
            'education.ssc.group' => ['required', 'string', 'max:60'],
            'education.ssc.passing_year' => ['required', 'integer', 'between:1950,' . $currentYear],

            'education.hsc.examination' => ['required', 'string', 'max:120'],
            'education.hsc.education_board' => ['required', 'string', 'max:120'],
            'education.hsc.result_type' => ['required', 'string', Rule::in($resultTypes)],
            'education.hsc.result' => ['nullable', 'numeric', 'min:0', 'required_if:education.hsc.result_type,numeric'],
            'education.hsc.result_scale' => ['nullable', 'numeric', 'min:0', 'required_if:education.hsc.result_type,numeric'],
            'education.hsc.division' => ['nullable', 'string', Rule::in($divisions), 'required_if:education.hsc.result_type,division'],
            'education.hsc.group' => ['required', 'string', 'max:60'],
            'education.hsc.passing_year' => ['required', 'integer', 'between:1950,' . $currentYear],

            'education.graduation.examination' => ['required', 'string', 'max:120'],
            'education.graduation.subject' => ['required', 'string', 'max:120'],
            'education.graduation.institution' => ['required', 'string', 'max:255'],
            'education.graduation.result_type' => ['required', 'string', Rule::in($resultTypes)],
            'education.graduation.result' => ['nullable', 'numeric', 'min:0', 'required_if:education.graduation.result_type,numeric'],
            'education.graduation.result_scale' => ['nullable', 'numeric', 'min:0', 'required_if:education.graduation.result_type,numeric'],
            'education.graduation.division' => ['nullable', 'string', Rule::in($divisions), 'required_if:education.graduation.result_type,division'],
            'education.graduation.passing_year' => ['required', 'integer', 'between:1950,' . $currentYear],
            'education.graduation.course_duration_years' => ['required', 'numeric', 'min:1', 'max:10'],

            'education.masters.examination' => ['nullable', 'string', 'max:120'],
            'education.masters.subject' => ['nullable', 'string', 'max:120'],
            'education.masters.institution' => ['nullable', 'string', 'max:255'],
            'education.masters.result_type' => ['nullable', 'string', Rule::in($resultTypes)],
            'education.masters.result' => ['nullable', 'numeric', 'min:0', 'required_if:education.masters.result_type,numeric'],
            'education.masters.result_scale' => ['nullable', 'numeric', 'min:0', 'required_if:education.masters.result_type,numeric'],
            'education.masters.division' => ['nullable', 'string', Rule::in($divisions), 'required_if:education.masters.result_type,division'],
            'education.masters.passing_year' => ['nullable', 'integer', 'between:1950,' . $currentYear],
            'education.masters.course_duration_years' => ['nullable', 'numeric', 'min:1', 'max:10'],

            'education.mphil_phd.subject' => ['nullable', 'string', 'max:120'],
            'education.mphil_phd.institution' => ['nullable', 'string', 'max:255'],
            'education.mphil_phd.degree_completion' => ['nullable', 'string', Rule::in(['degree_awarded', 'ongoing'])],
            'education.mphil_phd.completion_year' => ['nullable', 'integer', 'between:1950,' . $currentYear],

            'education_documents.ssc.certificate' => ['required', 'file', 'mimes:pdf', 'max:'.$certificateMaxKb],
            'education_documents.hsc.certificate' => ['required', 'file', 'mimes:pdf', 'max:'.$certificateMaxKb],
            'education_documents.graduation.certificate' => ['required', 'file', 'mimes:pdf', 'max:'.$certificateMaxKb],
            'education_documents.masters.certificate' => ['nullable', 'file', 'mimes:pdf', 'max:'.$certificateMaxKb],

            'job_experience.total_years' => ['required', 'numeric', 'min:0', 'max:60'],
            'job_experience.current.job_category' => ['nullable', 'string', 'max:120'],
            'job_experience.current.organization_name' => ['nullable', 'string', 'max:255'],
            'job_experience.current.designation' => ['nullable', 'string', 'max:255'],
            'job_experience.current.address' => ['nullable', 'string', 'max:255'],
            'job_experience.current.starting_date' => ['nullable', 'date'],
            'job_experience.previous.job_category' => ['nullable', 'string', 'max:120'],
            'job_experience.previous.organization_name' => ['nullable', 'string', 'max:255'],
            'job_experience.previous.designation' => ['nullable', 'string', 'max:255'],
            'job_experience.previous.address' => ['nullable', 'string', 'max:255'],
            'job_experience.previous.starting_date' => ['nullable', 'date'],
            'job_experience.previous.ending_date' => ['nullable', 'date', 'after_or_equal:job_experience.previous.starting_date'],

            'course_preferences.first_choice' => ['required', 'string', Rule::in($programs)],
            'course_preferences.second_choice' => ['required', 'string', Rule::in($programs), 'different:course_preferences.first_choice'],
            'course_preferences.third_choice' => ['required', 'string', Rule::in($programs), 'different:course_preferences.first_choice', 'different:course_preferences.second_choice'],
            'course_preferences.fourth_choice' => ['required', 'string', Rule::in($programs), 'different:course_preferences.first_choice', 'different:course_preferences.second_choice', 'different:course_preferences.third_choice'],
            'course_preferences.fifth_choice' => ['required', 'string', Rule::in($programs), 'different:course_preferences.first_choice', 'different:course_preferences.second_choice', 'different:course_preferences.third_choice', 'different:course_preferences.fourth_choice'],
            'course_preferences.sixth_choice' => ['required', 'string', Rule::in($programs), 'different:course_preferences.first_choice', 'different:course_preferences.second_choice', 'different:course_preferences.third_choice', 'different:course_preferences.fourth_choice', 'different:course_preferences.fifth_choice'],

            'declaration' => ['accepted'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->validateResultNotExceedsScale($validator, 'ssc');
            $this->validateResultNotExceedsScale($validator, 'hsc');
            $this->validateResultNotExceedsScale($validator, 'graduation');
            $this->validateResultNotExceedsScale($validator, 'masters');
        });
    }

    private function validateResultNotExceedsScale(Validator $validator, string $level): void
    {
        $resultType = $this->input("education.{$level}.result_type", 'numeric');
        if ($resultType !== 'numeric') {
            return;
        }

        $result = $this->input("education.{$level}.result");
        $scale  = $this->input("education.{$level}.result_scale");

        if ($result === null || $result === '' || $scale === null || $scale === '') {
            return;
        }

        $resultNum = (float) $result;
        $scaleNum  = (float) $scale;

        if ($resultNum > $scaleNum) {
            $label = ucfirst($level);
            $validator->errors()->add(
                "education.{$level}.result",
                "{$label} result ({$resultNum}) cannot be greater than the result scale ({$scaleNum})."
            );
        }
    }
}
