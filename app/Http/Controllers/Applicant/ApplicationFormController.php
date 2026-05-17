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
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ApplicationFormController extends Controller
{
    public function create(Exam $exam): View|RedirectResponse
    {
        abort_unless($this->isExamOpenForApplication($exam), 404);


        // Get sample paid applications for dev form fillup (local mode only)
        $devSampleApplications = [];
        if (app()->isLocal()) {
            $devSampleApplications = Application::query()
                ->where('exam_id', $exam->id)
                ->where('status', 'paid')
                ->limit(5)
                ->get(['applicant_name', 'applicant_email', 'applicant_phone', 'applicant_nid'])
                ->toArray();
        }

        $districts = Category::query()
            ->where('type', 'district')
            ->orderBy('name')
            ->get(['id', 'name']);

        $upazilas = Category::query()
            ->whereIn('type', ['upazila', 'thana'])
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id', 'type']);

        return view('pages.application-form', [
            'exam' => $exam,
            'formOptions' => config('applicant_form'),
            'districts' => $districts,
            'upazilas' => $upazilas,
            'uploadRules' => [
                'photo' => config('applicant_uploads.photo', []),
                'signature' => config('applicant_uploads.signature', []),
                'certificate_pdf' => config('applicant_uploads.certificate_pdf', []),
            ],
            'devSampleApplications' => $devSampleApplications,
            'isLocal' => app()->isLocal(),
        ]);
    }

    public function store(StoreApplicationRequest $request, Exam $exam): RedirectResponse
    {
        abort_unless($this->isExamOpenForApplication($exam), 404);

        $validated = $request->validated();
        $validated['mobile_number'] = '+880' . ($validated['mobile_number_local'] ?? '');
        unset($validated['mobile_number_local']);
        $validated['education'] = $this->normalizeEducationData($validated['education'] ?? []);

        // Block duplicate paid submissions: same email, phone, or NID for the same exam
        // For one exam, a paid user should be unique - one application per user per exam
        $duplicateApplication = Application::query()
            ->where('exam_id', $exam->id)
            ->where('status', 'paid')
            ->where(function ($q) use ($validated): void {
                $q->where('applicant_email', $validated['email'])
                  ->orWhere('applicant_phone', $validated['mobile_number'])
                   ->orWhere('applicant_nid', $validated['national_id_number']);
            })
            ->first();

        if ($duplicateApplication) {
            Log::warning('Duplicate paid application attempt blocked', [
                'exam_id' => $exam->id,
                'email' => $validated['email'],
                'phone' => $validated['mobile_number'],
                'nid' => $validated['national_id_number'],
                'existing_application_id' => $duplicateApplication->application_id,
            ]);

            // Provide specific error message based on which field caused the duplicate
            $matchField = 'email, phone, or ID number';
            if ($duplicateApplication->applicant_email === $validated['email']) {
                $matchField = 'email address';
            } elseif ($duplicateApplication->applicant_phone === $validated['mobile_number']) {
                $matchField = 'phone number';
            } elseif ($duplicateApplication->applicant_nid === $validated['national_id_number']) {
                $matchField = 'ID number';
            }

            return back()
                ->withInput()
                ->withErrors([
                    'applicant_name' => 'You have already successfully applied and paid for this exam.' .
                                       'You cannot submit another application for the same exam with the same ' . $matchField . '. ' .
                                       'If you believe this is an error, please contact the office.',
                ]);
        }

        // Upload files first (filesystem operations must happen outside DB transaction).
        // On any subsequent DB failure we delete these orphaned files in the catch block.
        $applicantPhotoPath = $request->file('applicant_photo')->storePublicly('applicant_uploads/photos', 'public');
        $signaturePath      = $request->file('signature')->storePublicly('applicant_uploads/signatures', 'public');

        $educationDocumentPaths = [
            'ssc' => [
                'certificate' => $request->file('education_documents.ssc.certificate')?->storePublicly('applicant_uploads/education/ssc', 'public'),
            ],
            'hsc' => [
                'certificate' => $request->file('education_documents.hsc.certificate')?->storePublicly('applicant_uploads/education/hsc', 'public'),
            ],
            'graduation' => [
                'certificate' => $request->file('education_documents.graduation.certificate')?->storePublicly('applicant_uploads/education/graduation', 'public'),
            ],
            'masters' => [
                'certificate' => $request->file('education_documents.masters.certificate')?->storePublicly('applicant_uploads/education/masters', 'public'),
            ],
        ];

        $dob = Carbon::parse($validated['date_of_birth']);
        $ageDiff = $dob->diff(now());
        $computedAge = sprintf('%d Years, %d Months', $ageDiff->y, $ageDiff->m);

        $presentDistrict = Category::query()->find($validated['present_address']['district_id'], ['id', 'name']);
        $presentUpazila = Category::query()->find($validated['present_address']['upazila_id'], ['id', 'name']);
        $permanentDistrict = Category::query()->find($validated['permanent_address']['district_id'], ['id', 'name']);
        $permanentUpazila = Category::query()->find($validated['permanent_address']['upazila_id'], ['id', 'name']);

        try {
            $application = Application::create([
                'exam_id' => $exam->id,
                'applicant_name' => $validated['applicant_name'],
                'applicant_email' => $validated['email'],
                'applicant_phone' => $validated['mobile_number'],
                'applicant_nid' => $validated['national_id_number'],
                'gender' => $validated['gender'],
                'status' => 'submitted',
                'additional_info' => [
                    'source' => 'homepage_stepper_form',
                    'exam_name' => $exam->name,
                    'personal' => [
                        'father_name' => $validated['father_name'],
                        'mother_name' => $validated['mother_name'],
                        'gender' => $validated['gender'],
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
                    'confirmations' => [
                        'declaration' => (bool) ($validated['declaration'] ?? false),
                        'contact_info_confirmation' => (bool) ($validated['contact_info_confirmation'] ?? false),
                    ],
                    'uploads' => [
                        'applicant_photo' => $applicantPhotoPath,
                        'signature' => $signaturePath,
                        'education_documents' => $educationDocumentPaths,
                    ],
                ],
            ]);

            Log::info('New application created', [
                'application_id' => $application->application_id,
                'exam_id' => $exam->id,
                'applicant_email' => $application->applicant_email,
            ]);
        } catch (\Throwable $e) {
            // Delete all files that were uploaded before the DB failure to avoid permanent orphans.
            $allUploadedPaths = array_values(array_filter(array_merge(
                [$applicantPhotoPath, $signaturePath],
                Arr::flatten($educationDocumentPaths)
            )));
            Storage::disk('public')->delete($allUploadedPaths);

            Log::error('Application create failed; cleaned up uploaded files', [
                'exam_id' => $exam->id,
                'files_deleted' => count($allUploadedPaths),
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'applicant_name' => 'Your application could not be saved due to a server error. Please try again.',
                ]);
        }

        return redirect()
            ->route('payment.initiate', $application)
            ->with('status', 'Application submitted successfully. Please complete payment to finalize your submission.');
    }

    /**
     * Check if user (by email and phone) has already paid for this exam
     * Returns JSON response with duplicate application info if exists
     */
    public function checkUserUniqueness(Exam $exam): \Illuminate\Http\JsonResponse
    {
        abort_unless($this->isExamOpenForApplication($exam), 404);

        $email = request()->input('email');
        $phone = request()->input('phone');

        if (!$email || !$phone) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email and phone are required',
            ], 400);
        }

        // Normalize phone number to +880 format
        $phoneDigits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($phoneDigits, '880')) {
            $normalizedPhone = '+' . $phoneDigits;
        } else {
            // Remove leading 0 if exists
            $phoneDigits = ltrim($phoneDigits, '0');
            $normalizedPhone = '+880' . $phoneDigits;
        }

        // Check if this user (email + phone) already has a paid application for this exam
        $duplicateApplication = Application::query()
            ->where('exam_id', $exam->id)
            ->where('status', 'paid')
            ->where(function ($q) use ($email, $normalizedPhone): void {
                $q->where('applicant_email', $email)
                  ->where('applicant_phone', $normalizedPhone);
            })
            ->first();

        if ($duplicateApplication) {
            Log::warning('User uniqueness check failed - duplicate paid application', [
                'exam_id' => $exam->id,
                'email' => $email,
                'phone' => $normalizedPhone,
                'existing_application_id' => $duplicateApplication->application_id,
            ]);

            return response()->json([
                'status' => 'duplicate',
                'isDuplicate' => true,
                'message' => 'You have already successfully applied and paid for this exam. Your existing application is active.',
                'applicationId' => $duplicateApplication->application_id,
                'existingApplicationEmail' => $duplicateApplication->applicant_email,
                'existingApplicationPhone' => $duplicateApplication->applicant_phone,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'isDuplicate' => false,
            'message' => 'You can proceed with the application.',
        ]);
    }

    private function isExamOpenForApplication(Exam $exam): bool
    {
        return Exam::query()
            ->availableForApplication()
            ->whereKey($exam->getKey())
            ->exists();
    }

    private function normalizeEducationData(array $education): array
    {
        foreach (['ssc', 'hsc', 'graduation', 'masters', 'mphil_phd'] as $level) {
            $row = $education[$level] ?? null;
            if (! is_array($row)) {
                continue;
            }

            $resultType = (string) ($row['result_type'] ?? 'numeric');
            if ($resultType === 'division') {
                $division = data_get($row, 'division');
                $row['result'] = $division;
                $row['result_scale'] = 'Division';
            } else {
                $row['result_type'] = 'numeric';
            }

            $education[$level] = $row;
        }

        if (isset($education['mphil_phd']) && is_array($education['mphil_phd'])) {
            unset(
                $education['mphil_phd']['result_type'],
                $education['mphil_phd']['result'],
                $education['mphil_phd']['result_scale'],
                $education['mphil_phd']['division']
            );
        }

        return $education;
    }
}

