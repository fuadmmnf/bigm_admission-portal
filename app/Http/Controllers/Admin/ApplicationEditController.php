<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateApplicationRequest;
use App\Models\Application;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

class ApplicationEditController extends Controller
{
    public function edit(Application $application): View
    {
        $extra = is_array($application->additional_info) ? $application->additional_info : [];

        $mobilePhone = $application->applicant_phone ?? '';
        $mobileLocal = preg_replace('/\D+/', '', $mobilePhone);
        if (str_starts_with($mobileLocal, '880')) {
            $mobileLocal = substr($mobileLocal, 3);
        }
        if (str_starts_with($mobileLocal, '0')) {
            $mobileLocal = substr($mobileLocal, 1);
        }

        $districts = Category::query()
            ->where('type', 'district')
            ->orderBy('name')
            ->get(['id', 'name']);

        $upazilas = Category::query()
            ->whereIn('type', ['upazila', 'thana'])
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id', 'type']);

        return view('pages.admin-application-edit', [
            'application' => $application,
            'exam' => $application->exam,
            'formOptions' => config('applicant_form'),
            'districts' => $districts,
            'upazilas' => $upazilas,
            'mobileLocal' => $mobileLocal,
            'uploadRules' => [
                'photo' => config('applicant_uploads.photo', []),
                'signature' => config('applicant_uploads.signature', []),
                'certificate_pdf' => config('applicant_uploads.certificate_pdf', []),
            ],
            'isLocal' => app()->isLocal(),
            'additionalInfo' => $extra,
        ]);
    }

    public function update(UpdateApplicationRequest $request, Application $application): RedirectResponse
    {
        $validated = $request->validated();
        $validated['mobile_number'] = '+880' . ($validated['mobile_number_local'] ?? '');
        unset($validated['mobile_number_local']);
        $validated['education'] = $this->normalizeEducationData($validated['education'] ?? []);

        $dob = Carbon::parse($validated['date_of_birth']);
        $ageDiff = $dob->diff(now());
        $computedAge = sprintf('%d Years, %d Months', $ageDiff->y, $ageDiff->m);

        $presentDistrict = Category::query()->find($validated['present_address']['district_id'], ['id', 'name']);
        $presentUpazila = Category::query()->find($validated['present_address']['upazila_id'], ['id', 'name']);
        $permanentDistrict = Category::query()->find($validated['permanent_address']['district_id'], ['id', 'name']);
        $permanentUpazila = Category::query()->find($validated['permanent_address']['upazila_id'], ['id', 'name']);

        $additionalInfo = $application->additional_info ?? [];

        $additionalInfo['personal'] = [
            'gender' => $validated['gender'],
            'father_name' => $validated['father_name'],
            'mother_name' => $validated['mother_name'],
            'date_of_birth' => $validated['date_of_birth'],
            'age_as_of_reference' => $computedAge,
        ];

        $additionalInfo['present_address'] = [
            'district_id' => $validated['present_address']['district_id'],
            'district_name' => $presentDistrict?->name,
            'upazila_id' => $validated['present_address']['upazila_id'],
            'upazila_name' => $presentUpazila?->name,
            'post_office' => $validated['present_address']['post_office'],
            'post_code' => $validated['present_address']['post_code'],
            'address_line' => $validated['present_address']['address_line'],
        ];

        $additionalInfo['permanent_address'] = [
            'district_id' => $validated['permanent_address']['district_id'],
            'district_name' => $permanentDistrict?->name,
            'upazila_id' => $validated['permanent_address']['upazila_id'],
            'upazila_name' => $permanentUpazila?->name,
            'post_office' => $validated['permanent_address']['post_office'],
            'post_code' => $validated['permanent_address']['post_code'],
            'address_line' => $validated['permanent_address']['address_line'],
        ];

        $additionalInfo['education'] = $validated['education'];

        $additionalInfo['job_experience'] = $validated['job_experience'];

        $additionalInfo['course_preferences'] = $validated['course_preferences'];

        $application->update([
            'applicant_name' => $validated['applicant_name'],
            'applicant_email' => $validated['email'],
            'applicant_phone' => $validated['mobile_number'],
            'applicant_nid' => $validated['national_id_number'],
            'gender' => $validated['gender'],
            'additional_info' => $additionalInfo,
        ]);

        return redirect()
            ->route('admin.applications.show', $application)
            ->with('status', 'Application details updated successfully.');
    }

    private function normalizeEducationData(array $education): array
    {
        foreach (['ssc', 'hsc', 'graduation', 'masters', 'mphil_phd'] as $level) {
            if (!isset($education[$level])) {
                $education[$level] = [];
            }

            $data = &$education[$level];

            if (data_get($data, 'result_type') === 'numeric') {
                if (!isset($data['result_scale'])) {
                    $data['result_scale'] = null;
                }
            } elseif (data_get($data, 'result_type') === 'division') {
                unset($data['result'], $data['result_scale']);
            }
        }

        return $education;
    }
}
