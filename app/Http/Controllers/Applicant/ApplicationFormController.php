<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Applicant\StoreApplicationRequest;
use App\Models\Application;
use App\Models\Category;
use App\Models\Exam;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ApplicationFormController extends Controller
{
    public function create(Exam $exam): View
    {
        abort_unless($this->isExamOpenForApplication($exam), 404);

        $districts = Category::query()
            ->where('type', 'district')
            ->orderBy('name')
            ->get(['id', 'name']);

        $upazilas = Category::query()
            ->where('type', 'upazila')
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);

        return view('pages.application-form', [
            'exam' => $exam,
            'formOptions' => config('applicant_form'),
            'districts' => $districts,
            'upazilas' => $upazilas,
            'uploadRules' => [
                'photo' => config('applicant_uploads.photo', []),
                'signature' => config('applicant_uploads.signature', []),
                'marksheet_pdf' => config('applicant_uploads.marksheet_pdf', []),
            ],
        ]);
    }

    public function store(StoreApplicationRequest $request, Exam $exam): RedirectResponse
    {
        abort_unless($this->isExamOpenForApplication($exam), 404);

        $validated = $request->validated();

        // Block duplicate paid submissions: same email, phone, or NID for the same exam
        $alreadyPaid = Application::query()
            ->where('exam_id', $exam->id)
            ->where('status', 'paid')
            ->where(function ($q) use ($validated): void {
                $q->where('applicant_email', $validated['email'])
                  ->orWhere('applicant_phone', $validated['mobile_number'])
                  ->orWhere('applicant_id_number', $validated['national_id_number']);
            })
            ->exists();

        if ($alreadyPaid) {
            return back()
                ->withInput()
                ->withErrors([
                    'applicant_name' => 'A paid application already exists for this exam with the same email, phone, or ID number. Please contact the office if you believe this is an error.',
                ]);
        }

        $applicantPhotoPath = $request->file('applicant_photo')?->store('applicant_uploads/photos', 'public');
        $signaturePath = $request->file('signature')?->store('applicant_uploads/signatures', 'public');

        $dob = Carbon::parse($validated['date_of_birth']);
        $ageDiff = $dob->diff(now());
        $computedAge = sprintf('%d Years, %d Months', $ageDiff->y, $ageDiff->m);

        $presentDistrict = Category::query()->find($validated['present_address']['district_id'], ['id', 'name']);
        $presentUpazila = Category::query()->find($validated['present_address']['upazila_id'], ['id', 'name']);
        $permanentDistrict = Category::query()->find($validated['permanent_address']['district_id'], ['id', 'name']);
        $permanentUpazila = Category::query()->find($validated['permanent_address']['upazila_id'], ['id', 'name']);

        $application = Application::create([
            'exam_id' => $exam->id,
            'applicant_name' => $validated['applicant_name'],
            'applicant_email' => $validated['email'],
            'applicant_phone' => $validated['mobile_number'],
            'applicant_id_number' => $validated['national_id_number'],
            'status' => 'submitted',
            'additional_info' => [
                'source' => 'homepage_stepper_form',
                'exam_name' => $exam->name,
                'personal' => [
                    'father_name' => $validated['father_name'],
                    'mother_name' => $validated['mother_name'],
                    'date_of_birth' => $validated['date_of_birth'],
                    'age_as_of_reference' => $computedAge,
                ],
                'present_address' => [
                    'district_id' => $validated['present_address']['district_id'],
                    'district_name' => $presentDistrict?->name,
                    'upazila_id' => $validated['present_address']['upazila_id'],
                    'upazila_name' => $presentUpazila?->name,
                    'post_office' => $validated['present_address']['post_office'],
                    'post_code' => $validated['present_address']['post_code'],
                    'address_line' => $validated['present_address']['address_line'],
                ],
                'permanent_address' => [
                    'district_id' => $validated['permanent_address']['district_id'],
                    'district_name' => $permanentDistrict?->name,
                    'upazila_id' => $validated['permanent_address']['upazila_id'],
                    'upazila_name' => $permanentUpazila?->name,
                    'post_office' => $validated['permanent_address']['post_office'],
                    'post_code' => $validated['permanent_address']['post_code'],
                    'address_line' => $validated['permanent_address']['address_line'],
                ],
                'education' => $validated['education'],
                'job_experience' => $validated['job_experience'],
                'course_preferences' => $validated['course_preferences'],
                'uploads' => [
                    'applicant_photo' => $applicantPhotoPath,
                    'signature' => $signaturePath,
                ],
            ],
        ]);

        return redirect()
            ->route('payment.initiate', $application)
            ->with('status', 'Application submitted successfully. Please complete payment to finalize your submission.');
    }

    private function isExamOpenForApplication(Exam $exam): bool
    {
        return Exam::query()
            ->availableForApplication()
            ->whereKey($exam->getKey())
            ->exists();
    }
}

