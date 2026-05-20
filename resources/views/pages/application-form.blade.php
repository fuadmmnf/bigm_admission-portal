<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Form - {{ $exam->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900" x-data="{ showIntroModal: {{ old('applicant_name') ? 'false' : 'true' }} }">

    {{-- Payment failure / cancel flash banner – bottom-centre, solid colours --}}
    @if(session('payment_error') || session('payment_info'))
    <div
        x-data="{ show: true }"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="fixed bottom-6 inset-x-0 z-[9999] flex justify-center px-4 pointer-events-none"
    >
        @if(session('payment_error'))
        <div class="pointer-events-auto flex items-start gap-3 w-full max-w-xl rounded-xl shadow-2xl px-5 py-4 text-sm bg-red-600 text-white ring-1 ring-red-700">
            <svg class="mt-0.5 h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008z" />
            </svg>
            <span class="flex-1 font-semibold">{{ session('payment_error') }}</span>
            <button @click="show = false" class="opacity-80 hover:opacity-100 transition text-lg leading-none">&times;</button>
        </div>
        @else
        <div class="pointer-events-auto flex items-start gap-3 w-full max-w-xl rounded-xl shadow-2xl px-5 py-4 text-sm bg-amber-500 text-white ring-1 ring-amber-600">
            <svg class="mt-0.5 h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
            </svg>
            <span class="flex-1 font-semibold">{{ session('payment_info') }}</span>
            <button @click="show = false" class="opacity-80 hover:opacity-100 transition text-lg leading-none">&times;</button>
        </div>
        @endif
    </div>
    @endif
    @php
        $errorKeys = $errors->keys();
        $initialStep = 1;
        $hasAnyErrors = $errors->any();
        $photoRules = $uploadRules['photo'] ?? [];
        $signatureRules = $uploadRules['signature'] ?? [];
        $certificateRules = $uploadRules['certificate_pdf'] ?? [];
        $applicationFee = (float) config('sslcommerz.default_amount', 0);
        $educationResultTypes = $formOptions['education_result_types'] ?? ['numeric' => 'GPA/CGPA', 'division' => 'Division'];
        $educationDivisions = $formOptions['education_divisions'] ?? ['First Division', 'Second Division', 'Third Division'];
        $applicationStartAt = optional($exam->start_date)->format('d M Y, h:i A');
        $applicationEndAt = optional($exam->end_date)->format('d M Y, h:i A');
        $oldMobileDigits = preg_replace('/\D+/', '', (string) old('mobile_number_local', old('mobile_number', '')));
        if (str_starts_with($oldMobileDigits, '880')) {
            $oldMobileDigits = substr($oldMobileDigits, 3);
        }
        if (str_starts_with($oldMobileDigits, '0')) {
            $oldMobileDigits = substr($oldMobileDigits, 1);
        }

        // No prefill upload paths — files are always deleted on failed/cancelled payment.
        $existingPhoto     = '';
        $existingSignature = '';
        $existingEduDocs   = [];
        $initialPhotoUrl     = null;
        $initialSignatureUrl = null;
        $initialPdfUrls      = array_fill_keys([
            'ssc_certificate','hsc_certificate','graduation_certificate','masters_certificate',
        ], null);

        $hasUploadErrors = collect($errorKeys)->contains(function ($errorKey) {
            return $errorKey === 'applicant_photo'
                || $errorKey === 'signature'
                || str_starts_with($errorKey, 'education_documents.');
        });

        foreach ($errorKeys as $errorKey) {
            if (
                str_starts_with($errorKey, 'present_address.') ||
                str_starts_with($errorKey, 'permanent_address.')
            ) {
                $initialStep = 2;
                break;
            }

            if (str_starts_with($errorKey, 'education.') || str_starts_with($errorKey, 'education_documents.')) {
                $initialStep = 3;
                break;
            }

            if (str_starts_with($errorKey, 'job_experience.')) {
                $initialStep = 4;
                break;
            }

            if (str_starts_with($errorKey, 'course_preferences.')) {
                $initialStep = 5;
                break;
            }

            if (in_array($errorKey, ['declaration', 'contact_info_confirmation'], true)) {
                $initialStep = 6;
                break;
            }
        }

        // Always return applicants to step 1 when the submission fails.
        if ($hasAnyErrors) {
            $initialStep = 1;
        }
    @endphp

    <div
        x-show="showIntroModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/70 backdrop-blur-sm p-4"
    >
        <div class="w-full max-w-3xl max-h-[90vh] flex flex-col rounded-xl bg-white shadow-2xl border border-gray-200">
            <div class="px-6 py-5 border-b border-gray-200 flex-shrink-0">
                <h2 class="text-xl font-bold text-gray-900">Read Before You Start Application</h2>
                <p class="text-sm text-gray-600 mt-1">Please review these instructions carefully. Application fields will be enabled after you confirm.</p>
            </div>

            <div class="px-6 py-5 space-y-5 text-sm text-gray-700 overflow-y-auto flex-1">
                <div class="rounded-lg border border-indigo-100 bg-indigo-50 p-4">
                    <p class="font-semibold text-indigo-900">Application Window</p>
                    <p class="mt-1 text-indigo-800">
                        Start: <strong>{{ $applicationStartAt ?? 'Immediately' }}</strong><br>
                        End: <strong>{{ $applicationEndAt ?? 'Until exam closes' }}</strong>
                    </p>
                </div>

                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                    <p class="font-semibold text-emerald-900">Payment Information</p>
                    <p class="mt-1 text-emerald-800">
                        Application Fee: <strong>BDT {{ number_format($applicationFee, 2) }}</strong>
                    </p>
                </div>

                <div>
                    <p class="font-semibold text-gray-900 mb-2">Required Upload Dimensions & Limits</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Photo: {{ data_get($photoRules, 'width', 300) }}x{{ data_get($photoRules, 'height', 300) }} px, max {{ data_get($photoRules, 'max_kb', 1024) }} KB.</li>
                        <li>Signature: {{ data_get($signatureRules, 'width', 300) }}x{{ data_get($signatureRules, 'height', 80) }} px, max {{ data_get($signatureRules, 'max_kb', 512) }} KB.</li>
                        <li>SSC, HSC, and other qualification certificate PDFs: max {{ data_get($certificateRules, 'max_kb', 5120) }} KB each.</li>
                    </ul>
                </div>

                <div>
                    <p class="font-semibold text-gray-900 mb-2">Stepper Breakdown</p>
                    <ol class="list-decimal list-inside space-y-1">
                        <li><strong>Personal</strong>: Applicant details, photo, and signature.</li>
                        <li><strong>Address</strong>: Present and permanent address.</li>
                        <li><strong>Education</strong>: SSC, HSC, Graduation, optional Masters, and optional MPhil/PhD details.</li>
                        <li><strong>Job Experience</strong>: Job experience (current and previous).</li>
                        <li><strong>Course Choice</strong>: 6 program preferences without duplication.</li>
                        <li><strong>Confirm</strong>: Declaration and final submission for payment.</li>
                    </ol>
                </div>

                <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-red-800">
                    <p class="font-semibold">Important Warning</p>
                    <ul class="list-disc list-inside mt-2 space-y-1">
                        <li>If payment fails or is cancelled, the submitted application will be deleted.</li>
                        <li>If the payment page is reloaded before completion, the application will be deleted.</li>
                        <li>If submission fails for validation/duplicate checks, your uploaded photo, signature, and certificate PDFs are cleared and must be uploaded again.</li>
                    </ul>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-end gap-2 flex-shrink-0">
                <a href="{{ route('home') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Back</a>
                <button
                    type="button"
                    x-on:click="showIntroModal = false"
                    class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                >
                    I Understand, Start Application
                </button>
            </div>
        </div>
    </div>

    <div :class="showIntroModal ? 'pointer-events-none select-none opacity-65 blur-[2px]' : ''">
    <header class="bg-white border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-indigo-700">Admission Application Form</h1>
                <p class="text-sm text-gray-600 mt-1">{{ $exam->name }}</p>
            </div>
            <a href="{{ route('home') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Back to Homepage</a>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-8">
        <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 mb-6 flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
            <span class="text-sm font-semibold text-blue-900">Application period:</span>
            <span class="text-sm text-blue-800">{{ $applicationStartAt ?? 'Now' }} to {{ $applicationEndAt ?? 'Until exam closes' }}</span>
            <span class="text-xs text-blue-500">&middot; Fill out all required fields from the original PDF form. After submission, you will be redirected to payment.</span>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4">
                <p class="font-semibold text-red-800">Please fix the highlighted errors and continue.</p>
                @if ($hasUploadErrors)
                    <p class="mt-2 text-sm font-medium text-red-700">Uploads are not retained after failed attempts. Please re-upload required photo/signature/certificate files.</p>
                @endif
                <ul class="mt-2 list-disc list-inside text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            action="{{ route('applications.store', $exam) }}"
            method="POST"
            enctype="multipart/form-data"
            x-on:submit.prevent="submitWithUniquenessCheck($event)"
            x-data="applicationStepper({
                initialStep: {{ $initialStep }},
                totalSteps: 6,
                districts: @js($districts),
                upazilas: @js($upazilas),
                presentDistrictId: @js(old('present_address.district_id')),
                presentUpazilaId: @js(old('present_address.upazila_id')),
                permanentDistrictId: @js(old('permanent_address.district_id')),
                permanentUpazilaId: @js(old('permanent_address.upazila_id')),
                initialDob: @js(old('date_of_birth')),
                initialAge: @js(old('age_as_of_reference')),
                initialSameAsPresent: @js((bool) old('same_as_present_address')),
                programs: @js($formOptions['programs']),
                initialCourseChoices: @js([
                    old('course_preferences.first_choice', ''),
                    old('course_preferences.second_choice', ''),
                    old('course_preferences.third_choice', ''),
                    old('course_preferences.fourth_choice', ''),
                    old('course_preferences.fifth_choice', ''),
                    old('course_preferences.sixth_choice', ''),
                ]),
                initialPhotoUrl: @js($initialPhotoUrl),
                initialSignatureUrl: @js($initialSignatureUrl),
                initialPdfUrls: @js($initialPdfUrls),
                initialUploadError: @js($hasUploadErrors),
                initialHasErrors: @js($hasAnyErrors),
                initialEducationResultTypes: @js([
                    'ssc' => old('education.ssc.result_type', 'numeric'),
                    'hsc' => old('education.hsc.result_type', 'numeric'),
                    'graduation' => old('education.graduation.result_type', 'numeric'),
                    'masters' => old('education.masters.result_type', ''),
                ]),
            })"
            class="space-y-6"
        >
            @csrf


            <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
                <div class="flex flex-wrap items-center gap-2 sm:gap-3" role="list" aria-label="Form steps">
                    <template x-for="stepIndex in totalSteps" :key="stepIndex">
                        <button
                            type="button"
                            class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium border"
                            :class="step === stepIndex ? 'bg-indigo-600 text-white border-indigo-600' : (stepIndex < step ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-50 text-gray-600 border-gray-200')"
                            x-on:click="goTo(stepIndex)"
                        >
                            <span x-text="stepIndex"></span>
                            <span class="ml-2" x-text="stepTitles[stepIndex - 1]"></span>
                        </button>
                    </template>
                </div>
                @if (app()->environment(['local', 'testing']))
                    <div class="mt-3 pt-3 border-t border-dashed border-amber-200">
                        <button
                            type="button"
                            x-on:click="fillDevData()"
                            class="inline-flex items-center rounded-md border border-amber-300 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100"
                        >
                            Dev Mode: Autofill Form
                        </button>
                    </div>
                @endif
            </div>

            <section x-show="step === 1" x-cloak class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 space-y-4">
                <h2 class="text-lg font-semibold text-gray-900">Step 1: Personal Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="applicant_name" class="block text-sm font-medium text-gray-700">Applicant's Name *</label>
                        <input id="applicant_name" name="applicant_name" type="text" value="{{ old('applicant_name') }}" class="mt-1 block w-full rounded-md border-gray-300" required>
                    </div>

                    <div>
                        <label for="father_name" class="block text-sm font-medium text-gray-700">Father's Name *</label>
                        <input id="father_name" name="father_name" type="text" value="{{ old('father_name') }}" class="mt-1 block w-full rounded-md border-gray-300" required>
                    </div>

                    <div>
                        <label for="mother_name" class="block text-sm font-medium text-gray-700">Mother's Name *</label>
                        <input id="mother_name" name="mother_name" type="text" value="{{ old('mother_name') }}" class="mt-1 block w-full rounded-md border-gray-300" required>
                    </div>

                    <div>
                        <label for="national_id_number" class="block text-sm font-medium text-gray-700">National ID / Birth Reg. / Passport *</label>
                        <input id="national_id_number" name="national_id_number" type="text" value="{{ old('national_id_number') }}" class="mt-1 block w-full rounded-md border-gray-300" required>
                    </div>

                    <div>
                        <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Date of Birth *</label>
                        <input id="date_of_birth" name="date_of_birth" type="date" value="{{ old('date_of_birth') }}" class="mt-1 block w-full rounded-md border-gray-300" required>
                    </div>

                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700">Gender *</label>
                        <select id="gender" name="gender" class="mt-1 block w-full rounded-md border-gray-300" required>
                            <option value="">Select Gender</option>
                            @foreach (($formOptions['genders'] ?? []) as $gender)
                                <option value="{{ $gender }}" @selected(old('gender') === $gender)>{{ $gender }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="age_as_of_reference" class="block text-sm font-medium text-gray-700">Age (as of today) *</label>
                        <input id="age_as_of_reference" type="text" x-model="ageDisplay" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50" disabled>
                        <input name="age_as_of_reference" type="hidden" :value="ageDisplay">
                    </div>

                    <div>
                        <label for="mobile_number" class="block text-sm font-medium text-gray-700">Mobile Number *</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <span class="inline-flex items-center rounded-l-md border border-r-0 border-gray-300 bg-gray-100 px-3 text-sm text-gray-700">+880</span>
                            <input
                                id="mobile_number"
                                name="mobile_number_local"
                                type="text"
                                inputmode="numeric"
                                pattern="1[0-9]{9}"
                                maxlength="10"
                                value="{{ $oldMobileDigits }}"
                                x-on:input="$event.target.value = ($event.target.value || '').replace(/\D+/g, '').slice(0, 10)"
                                class="block w-full rounded-r-md border-gray-300"
                                placeholder="1XXXXXXXXX"
                                required
                            >
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Enter 10 digits only. Country code +880 is added automatically.</p>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" class="mt-1 block w-full rounded-md border-gray-300" required>
                    </div>

                    <!-- User Uniqueness Check Alert -->
                    <div id="uniquenesAlert" x-show="showUniquenessAlert" x-cloak class="rounded-lg border-l-4 border-red-400 bg-red-50 p-4">
                        <div class="flex items-start gap-3">
                            <svg class="h-5 w-5 text-red-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            <div class="flex-1">
                                <h3 class="text-sm font-semibold text-red-800">Duplicate Application Found</h3>
                                <p class="text-sm text-red-700 mt-1" x-text="uniquenessMessage"></p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <label for="applicant_photo" class="block text-sm font-medium text-gray-700">Applicant Photo * (300x300 pixels, max 1MB)</label>
                            <div x-show="photoPreviewUrl" x-cloak class="rounded border border-gray-200 bg-gray-50 p-1">
                                <img :src="photoPreviewUrl" alt="Applicant photo preview" class="h-10 w-10 rounded object-cover border border-gray-200">
                            </div>
                        </div>
                        @if($existingPhoto)
                            <p class="mt-1 text-xs text-emerald-700 font-medium">✓ Previously uploaded photo retained. Upload a new file below to replace it.</p>
                        @endif
                        <input id="applicant_photo" name="applicant_photo" type="file" accept="image/*" x-on:change="handleImagePreview($event, 'photo')" class="mt-1 block w-full text-sm" required>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <label for="signature_input" class="block text-sm font-medium text-gray-700">Signature * (300x80 pixels, max 1MB)</label>
                            <div x-show="signaturePreviewUrl" x-cloak class="rounded border border-gray-200 bg-gray-50 p-1">
                                <img :src="signaturePreviewUrl" alt="Applicant signature preview" class="h-8 w-24 rounded object-contain border border-gray-200 bg-white">
                            </div>
                        </div>
                        @if($existingSignature)
                            <p class="mt-1 text-xs text-emerald-700 font-medium">✓ Previously uploaded signature retained. Upload a new file below to replace it.</p>
                        @endif
                        <input id="signature_input" name="signature" type="file" accept="image/*" x-on:change="handleImagePreview($event, 'signature')" class="mt-1 block w-full text-sm" required>
                    </div>
                </div>
            </section>

            <section x-show="step === 2" x-cloak class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 space-y-5">
                <h2 class="text-lg font-semibold text-gray-900">Step 2: Address Information</h2>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <fieldset class="rounded-lg border border-gray-200 p-4">
                        <legend class="px-2 text-sm font-semibold text-gray-700">Present Address *</legend>
                        <div class="grid grid-cols-1 gap-3 mt-2">

                            {{-- District combobox --}}
                            <div class="relative" :class="presentDistrictOpen ? 'pb-52' : ''">
                                <label class="block text-sm font-medium text-gray-700 mb-1">District *</label>
                                <input
                                    id="present_district_input"
                                    type="text"
                                    x-model="presentDistrictText"
                                    x-on:focus="closeAddressDropdowns(); presentDistrictOpen = true"
                                    x-on:input="presentDistrictId = ''; presentDistrictOpen = true"
                                    x-on:blur="setTimeout(() => { presentDistrictOpen = false; restoreLabel('presentDistrict') }, 150)"
                                    placeholder="Search and select district..."
                                    autocomplete="off"
                                    class="rounded-md border-gray-300 w-full"
                                >
                                <div x-show="presentDistrictOpen" x-cloak class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-48 overflow-y-auto">
                                    <template x-for="district in filteredDistricts(presentDistrictText)" :key="district.id">
                                        <button
                                            type="button"
                                            class="block w-full text-left px-3 py-2 text-sm hover:bg-indigo-50"
                                            :class="presentDistrictId === String(district.id) ? 'bg-indigo-100 font-semibold text-indigo-700' : 'text-gray-700'"
                                            x-on:mousedown.prevent="presentDistrictId = String(district.id); presentDistrictText = district.name; presentDistrictOpen = false; onDistrictChange('present')"
                                            x-text="district.name"
                                        ></button>
                                    </template>
                                    <div x-show="filteredDistricts(presentDistrictText).length === 0" class="px-3 py-2 text-sm text-gray-400 italic">No districts found</div>
                                </div>
                                <input type="hidden" name="present_address[district_id]" :value="presentDistrictId">
                            </div>

                            {{-- Upazila combobox --}}
                            <div class="relative" :class="presentUpazilaOpen ? 'pb-52' : ''">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Upazila / Thana *</label>
                                <input
                                    id="present_upazila_input"
                                    type="text"
                                    x-model="presentUpazilaText"
                                    x-on:focus="if (presentDistrictId) { closeAddressDropdowns(); presentUpazilaOpen = true }"
                                    x-on:input="presentUpazilaId = ''; presentUpazilaOpen = true"
                                    x-on:blur="setTimeout(() => { presentUpazilaOpen = false; restoreLabel('presentUpazila') }, 150)"
                                    placeholder="Search and select upazila/thana..."
                                    autocomplete="off"
                                    :disabled="!presentDistrictId"
                                    class="rounded-md border-gray-300 w-full disabled:bg-gray-100 disabled:cursor-not-allowed disabled:text-gray-400"
                                >
                                <div x-show="presentUpazilaOpen" x-cloak class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-48 overflow-y-auto">
                                    <template x-for="upazila in filteredUpazilas(presentDistrictId, presentUpazilaText)" :key="upazila.id">
                                        <button
                                            type="button"
                                            class="block w-full text-left px-3 py-2 text-sm hover:bg-indigo-50"
                                            :class="presentUpazilaId === String(upazila.id) ? 'bg-indigo-100 font-semibold text-indigo-700' : 'text-gray-700'"
                                            x-on:mousedown.prevent="presentUpazilaId = String(upazila.id); presentUpazilaText = locationLabel(upazila); presentUpazilaOpen = false; if (sameAsPresentAddress) syncPermanentFromPresent()"
                                            x-text="locationLabel(upazila)"
                                        ></button>
                                    </template>
                                    <div x-show="filteredUpazilas(presentDistrictId, presentUpazilaText).length === 0" class="px-3 py-2 text-sm text-gray-400 italic">No upazila/thana found</div>
                                </div>
                                <input type="hidden" name="present_address[upazila_id]" :value="presentUpazilaId">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Post Office *</label>
                                <input name="present_address[post_office]" type="text" value="{{ old('present_address.post_office') }}" x-on:input="if (sameAsPresentAddress) syncPermanentFromPresent()" placeholder="Post Office" class="rounded-md border-gray-300 w-full" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Post Code *</label>
                                <input name="present_address[post_code]" type="text" value="{{ old('present_address.post_code') }}" x-on:input="if (sameAsPresentAddress) syncPermanentFromPresent()" placeholder="Post Code" class="rounded-md border-gray-300 w-full" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Village / Road / House / Flat *</label>
                                <input name="present_address[address_line]" type="text" value="{{ old('present_address.address_line') }}" x-on:input="if (sameAsPresentAddress) syncPermanentFromPresent()" placeholder="Village/Road/House/Flat" class="rounded-md border-gray-300 w-full" required>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="rounded-lg border border-gray-200 p-4">
                        <legend class="px-2 text-sm font-semibold text-gray-700">Permanent Address *</legend>
                        <label class="mt-2 inline-flex items-center gap-2 rounded-md border border-indigo-100 bg-indigo-50 px-3 py-2 text-sm text-indigo-900">
                            <input type="checkbox" name="same_as_present_address" value="1" x-model="sameAsPresentAddress" x-on:change="toggleSameAsPresentAddress()" class="rounded border-gray-300 text-indigo-600">
                            <span>Same as present address</span>
                        </label>
                        <div class="grid grid-cols-1 gap-3 mt-2">

                            {{-- District combobox --}}
                            <div class="relative" :class="permanentDistrictOpen ? 'pb-52' : ''">
                                <label class="block text-sm font-medium text-gray-700 mb-1">District *</label>
                                <input
                                    id="permanent_district_input"
                                    type="text"
                                    x-model="permanentDistrictText"
                                    :readonly="sameAsPresentAddress"
                                    x-on:focus="if (sameAsPresentAddress) return; closeAddressDropdowns(); permanentDistrictOpen = true"
                                    x-on:input="if (sameAsPresentAddress) return; permanentDistrictId = ''; permanentDistrictOpen = true"
                                    x-on:blur="setTimeout(() => { permanentDistrictOpen = false; restoreLabel('permanentDistrict') }, 150)"
                                    placeholder="Search and select district..."
                                    autocomplete="off"
                                    class="rounded-md border-gray-300 w-full"
                                    :class="sameAsPresentAddress ? 'bg-gray-100 cursor-not-allowed' : ''"
                                >
                                <div x-show="permanentDistrictOpen" x-cloak class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-48 overflow-y-auto">
                                    <template x-for="district in filteredDistricts(permanentDistrictText)" :key="district.id">
                                        <button
                                            type="button"
                                            class="block w-full text-left px-3 py-2 text-sm hover:bg-indigo-50"
                                            :class="permanentDistrictId === String(district.id) ? 'bg-indigo-100 font-semibold text-indigo-700' : 'text-gray-700'"
                                            x-on:mousedown.prevent="permanentDistrictId = String(district.id); permanentDistrictText = district.name; permanentDistrictOpen = false; onDistrictChange('permanent')"
                                            x-text="district.name"
                                        ></button>
                                    </template>
                                    <div x-show="filteredDistricts(permanentDistrictText).length === 0" class="px-3 py-2 text-sm text-gray-400 italic">No districts found</div>
                                </div>
                                <input type="hidden" name="permanent_address[district_id]" :value="permanentDistrictId">
                            </div>

                            {{-- Upazila combobox --}}
                            <div class="relative" :class="permanentUpazilaOpen ? 'pb-52' : ''">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Upazila / Thana *</label>
                                <input
                                    id="permanent_upazila_input"
                                    type="text"
                                    x-model="permanentUpazilaText"
                                    :readonly="sameAsPresentAddress"
                                    x-on:focus="if (sameAsPresentAddress) return; if (permanentDistrictId) { closeAddressDropdowns(); permanentUpazilaOpen = true }"
                                    x-on:input="if (sameAsPresentAddress) return; permanentUpazilaId = ''; permanentUpazilaOpen = true"
                                    x-on:blur="setTimeout(() => { permanentUpazilaOpen = false; restoreLabel('permanentUpazila') }, 150)"
                                    placeholder="Search and select upazila/thana..."
                                    autocomplete="off"
                                    :disabled="!permanentDistrictId"
                                    class="rounded-md border-gray-300 w-full disabled:bg-gray-100 disabled:cursor-not-allowed disabled:text-gray-400"
                                    :class="sameAsPresentAddress ? 'bg-gray-100 cursor-not-allowed' : ''"
                                >
                                <div x-show="permanentUpazilaOpen" x-cloak class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-48 overflow-y-auto">
                                    <template x-for="upazila in filteredUpazilas(permanentDistrictId, permanentUpazilaText)" :key="upazila.id">
                                        <button
                                            type="button"
                                            class="block w-full text-left px-3 py-2 text-sm hover:bg-indigo-50"
                                            :class="permanentUpazilaId === String(upazila.id) ? 'bg-indigo-100 font-semibold text-indigo-700' : 'text-gray-700'"
                                            x-on:mousedown.prevent="permanentUpazilaId = String(upazila.id); permanentUpazilaText = locationLabel(upazila); permanentUpazilaOpen = false"
                                            x-text="locationLabel(upazila)"
                                        ></button>
                                    </template>
                                    <div x-show="filteredUpazilas(permanentDistrictId, permanentUpazilaText).length === 0" class="px-3 py-2 text-sm text-gray-400 italic">No upazila/thana found</div>
                                </div>
                                <input type="hidden" name="permanent_address[upazila_id]" :value="permanentUpazilaId">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Post Office *</label>
                                <input name="permanent_address[post_office]" type="text" value="{{ old('permanent_address.post_office') }}" :readonly="sameAsPresentAddress" placeholder="Post Office" class="rounded-md border-gray-300 w-full" :class="sameAsPresentAddress ? 'bg-gray-100' : ''" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Post Code *</label>
                                <input name="permanent_address[post_code]" type="text" value="{{ old('permanent_address.post_code') }}" :readonly="sameAsPresentAddress" placeholder="Post Code" class="rounded-md border-gray-300 w-full" :class="sameAsPresentAddress ? 'bg-gray-100' : ''" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Village / Road / House / Flat *</label>
                                <input name="permanent_address[address_line]" type="text" value="{{ old('permanent_address.address_line') }}" :readonly="sameAsPresentAddress" placeholder="Village/Road/House/Flat" class="rounded-md border-gray-300 w-full" :class="sameAsPresentAddress ? 'bg-gray-100' : ''" required>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </section>

            <section x-show="step === 3" x-cloak class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 space-y-6">
                <h2 class="text-lg font-semibold text-gray-900">Step 3: Education Information</h2>

                <fieldset class="rounded-lg border border-gray-200 p-4">
                    <legend class="px-2 text-sm font-semibold text-gray-700">SSC / Equivalent *</legend>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-2">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Examination *</label><select name="education[ssc][examination]" class="rounded-md border-gray-300 w-full" required><option value="">Select Examination</option>@foreach ($formOptions['ssc_examinations'] as $option)<option value="{{ $option }}" @selected(old('education.ssc.examination') === $option)>{{ $option }}</option>@endforeach</select></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Education Board *</label><select name="education[ssc][education_board]" class="rounded-md border-gray-300 w-full" required><option value="">Select Education Board</option>@foreach ($formOptions['education_boards'] as $option)<option value="{{ $option }}" @selected(old('education.ssc.education_board') === $option)>{{ $option }}</option>@endforeach</select></div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Result Style *</label>
                            <select name="education[ssc][result_type]" x-model="educationResultTypes.ssc" class="rounded-md border-gray-300 w-full" required>
                                @foreach ($educationResultTypes as $resultTypeKey => $resultTypeLabel)
                                    <option value="{{ $resultTypeKey }}">{{ $resultTypeLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div x-show="educationResultTypes.ssc === 'numeric'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Result Scale *</label>
                            <input name="education[ssc][result_scale]" type="number" step="0.01" min="0" value="{{ old('education.ssc.result_scale') }}" placeholder="e.g. 5.00" class="rounded-md border-gray-300 w-full" :required="educationResultTypes.ssc === 'numeric'">
                        </div>
                        <div x-show="educationResultTypes.ssc === 'numeric'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">GPA *</label>
                            <input name="education[ssc][result]" type="number" step="0.01" min="0" value="{{ old('education.ssc.result') }}" placeholder="e.g. 4.67" class="rounded-md border-gray-300 w-full" :required="educationResultTypes.ssc === 'numeric'">
                        </div>
                        <div x-show="educationResultTypes.ssc === 'division'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Division *</label>
                            <select name="education[ssc][division]" class="rounded-md border-gray-300 w-full" :required="educationResultTypes.ssc === 'division'">
                                <option value="">Select Division</option>
                                @foreach ($educationDivisions as $division)
                                    <option value="{{ $division }}" @selected(old('education.ssc.division') === $division)>{{ $division }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Group *</label><select name="education[ssc][group]" class="rounded-md border-gray-300 w-full" required><option value="">Select Group</option>@foreach ($formOptions['groups'] as $option)<option value="{{ $option }}" @selected(old('education.ssc.group') === $option)>{{ $option }}</option>@endforeach</select></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Passing Year *</label><input name="education[ssc][passing_year]" type="number" value="{{ old('education.ssc.passing_year') }}" placeholder="Passing Year" class="rounded-md border-gray-300 w-full" required></div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 mt-4">
                        <div>
                            <label for="ssc_certificate" class="flex items-center gap-2 text-sm font-medium text-gray-700">SSC Certificate PDF *<a x-show="pdfPreviewUrls.ssc_certificate" x-cloak :href="pdfPreviewUrls.ssc_certificate" target="_blank" rel="noopener" class="inline-flex items-center rounded-md border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">View Upload</a></label>
                             <input id="ssc_certificate" name="education_documents[ssc][certificate]" type="file" accept="application/pdf" x-on:change="handlePdfPreview($event, 'ssc_certificate')" class="mt-1 block w-full text-sm" required>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="rounded-lg border border-gray-200 p-4">
                    <legend class="px-2 text-sm font-semibold text-gray-700">HSC / Equivalent *</legend>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-2">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Examination *</label><select name="education[hsc][examination]" class="rounded-md border-gray-300 w-full" required><option value="">Select Examination</option>@foreach ($formOptions['hsc_examinations'] as $option)<option value="{{ $option }}" @selected(old('education.hsc.examination') === $option)>{{ $option }}</option>@endforeach</select></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Education Board *</label><select name="education[hsc][education_board]" class="rounded-md border-gray-300 w-full" required><option value="">Select Education Board</option>@foreach ($formOptions['education_boards'] as $option)<option value="{{ $option }}" @selected(old('education.hsc.education_board') === $option)>{{ $option }}</option>@endforeach</select></div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Result Style *</label>
                            <select name="education[hsc][result_type]" x-model="educationResultTypes.hsc" class="rounded-md border-gray-300 w-full" required>
                                @foreach ($educationResultTypes as $resultTypeKey => $resultTypeLabel)
                                    <option value="{{ $resultTypeKey }}">{{ $resultTypeLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div x-show="educationResultTypes.hsc === 'numeric'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Result Scale *</label>
                            <input name="education[hsc][result_scale]" type="number" step="0.01" min="0" value="{{ old('education.hsc.result_scale') }}" placeholder="e.g. 5.00" class="rounded-md border-gray-300 w-full" :required="educationResultTypes.hsc === 'numeric'">
                        </div>
                        <div x-show="educationResultTypes.hsc === 'numeric'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">GPA *</label>
                            <input name="education[hsc][result]" type="number" step="0.01" min="0" value="{{ old('education.hsc.result') }}" placeholder="e.g. 4.50" class="rounded-md border-gray-300 w-full" :required="educationResultTypes.hsc === 'numeric'">
                        </div>
                        <div x-show="educationResultTypes.hsc === 'division'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Division *</label>
                            <select name="education[hsc][division]" class="rounded-md border-gray-300 w-full" :required="educationResultTypes.hsc === 'division'">
                                <option value="">Select Division</option>
                                @foreach ($educationDivisions as $division)
                                    <option value="{{ $division }}" @selected(old('education.hsc.division') === $division)>{{ $division }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Group *</label><select name="education[hsc][group]" class="rounded-md border-gray-300 w-full" required><option value="">Select Group</option>@foreach ($formOptions['groups'] as $option)<option value="{{ $option }}" @selected(old('education.hsc.group') === $option)>{{ $option }}</option>@endforeach</select></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Passing Year *</label><input name="education[hsc][passing_year]" type="number" value="{{ old('education.hsc.passing_year') }}" placeholder="Passing Year" class="rounded-md border-gray-300 w-full" required></div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 mt-4">
                        <div>
                            <label for="hsc_certificate" class="flex items-center gap-2 text-sm font-medium text-gray-700">HSC Certificate PDF *<a x-show="pdfPreviewUrls.hsc_certificate" x-cloak :href="pdfPreviewUrls.hsc_certificate" target="_blank" rel="noopener" class="inline-flex items-center rounded-md border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">View Upload</a></label>
                             <input id="hsc_certificate" name="education_documents[hsc][certificate]" type="file" accept="application/pdf" x-on:change="handlePdfPreview($event, 'hsc_certificate')" class="mt-1 block w-full text-sm" required>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="rounded-lg border border-gray-200 p-4">
                    <legend class="px-2 text-sm font-semibold text-gray-700">Graduation / Equivalent *</legend>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-2">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Examination *</label><select name="education[graduation][examination]" class="rounded-md border-gray-300 w-full" required><option value="">Select Examination</option>@foreach ($formOptions['graduation_examinations'] as $option)<option value="{{ $option }}" @selected(old('education.graduation.examination') === $option)>{{ $option }}</option>@endforeach</select></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Subject *</label><input name="education[graduation][subject]" type="text" value="{{ old('education.graduation.subject') }}" placeholder="Subject" class="rounded-md border-gray-300 w-full" required></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">University / Institute *</label><input name="education[graduation][institution]" type="text" value="{{ old('education.graduation.institution') }}" placeholder="University / Institute" class="rounded-md border-gray-300 w-full" required></div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Result Style *</label>
                            <select name="education[graduation][result_type]" x-model="educationResultTypes.graduation" class="rounded-md border-gray-300 w-full" required>
                                @foreach ($educationResultTypes as $resultTypeKey => $resultTypeLabel)
                                    <option value="{{ $resultTypeKey }}">{{ $resultTypeLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div x-show="educationResultTypes.graduation === 'numeric'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Result Scale *</label>
                            <input name="education[graduation][result_scale]" type="number" step="0.01" min="0" value="{{ old('education.graduation.result_scale') }}" placeholder="e.g. 4.00" class="rounded-md border-gray-300 w-full" :required="educationResultTypes.graduation === 'numeric'">
                        </div>
                        <div x-show="educationResultTypes.graduation === 'numeric'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">CGPA *</label>
                            <input name="education[graduation][result]" type="number" step="0.01" min="0" value="{{ old('education.graduation.result') }}" placeholder="e.g. 3.75" class="rounded-md border-gray-300 w-full" :required="educationResultTypes.graduation === 'numeric'">
                        </div>
                        <div x-show="educationResultTypes.graduation === 'division'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Division *</label>
                            <select name="education[graduation][division]" class="rounded-md border-gray-300 w-full" :required="educationResultTypes.graduation === 'division'">
                                <option value="">Select Division</option>
                                @foreach ($educationDivisions as $division)
                                    <option value="{{ $division }}" @selected(old('education.graduation.division') === $division)>{{ $division }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Passing Year *</label><input name="education[graduation][passing_year]" type="number" value="{{ old('education.graduation.passing_year') }}" placeholder="Passing Year" class="rounded-md border-gray-300 w-full" required></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Course Duration (Years) *</label><input name="education[graduation][course_duration_years]" type="number" step="0.1" value="{{ old('education.graduation.course_duration_years') }}" placeholder="Course Duration (Years)" class="rounded-md border-gray-300 w-full" required></div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 mt-4">
                        <div>
                            <label for="graduation_certificate" class="flex items-center gap-2 text-sm font-medium text-gray-700">Graduation Certificate PDF *<a x-show="pdfPreviewUrls.graduation_certificate" x-cloak :href="pdfPreviewUrls.graduation_certificate" target="_blank" rel="noopener" class="inline-flex items-center rounded-md border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">View Upload</a></label>
                             <input id="graduation_certificate" name="education_documents[graduation][certificate]" type="file" accept="application/pdf" x-on:change="handlePdfPreview($event, 'graduation_certificate')" class="mt-1 block w-full text-sm" required>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="rounded-lg border border-gray-200 p-4">
                    <legend class="px-2 text-sm font-semibold text-gray-700">Masters / Equivalent (If Applicable)</legend>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-2">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Examination</label><input name="education[masters][examination]" type="text" value="{{ old('education.masters.examination') }}" placeholder="Examination" class="rounded-md border-gray-300 w-full"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Subject</label><input name="education[masters][subject]" type="text" value="{{ old('education.masters.subject') }}" placeholder="Subject" class="rounded-md border-gray-300 w-full"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">University / Institute</label><input name="education[masters][institution]" type="text" value="{{ old('education.masters.institution') }}" placeholder="University / Institute" class="rounded-md border-gray-300 w-full"></div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Result Style</label>
                            <select name="education[masters][result_type]" x-model="educationResultTypes.masters" class="rounded-md border-gray-300 w-full">
                                <option value="">Select Result Style</option>
                                @foreach ($educationResultTypes as $resultTypeKey => $resultTypeLabel)
                                    <option value="{{ $resultTypeKey }}">{{ $resultTypeLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div x-show="educationResultTypes.masters === 'numeric'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Result Scale</label>
                            <input name="education[masters][result_scale]" type="number" step="0.01" min="0" value="{{ old('education.masters.result_scale') }}" placeholder="e.g. 4.00" class="rounded-md border-gray-300 w-full">
                        </div>
                        <div x-show="educationResultTypes.masters === 'numeric'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Result</label>
                            <input name="education[masters][result]" type="number" step="0.01" min="0" value="{{ old('education.masters.result') }}" placeholder="Result" class="rounded-md border-gray-300 w-full">
                        </div>
                        <div x-show="educationResultTypes.masters === 'division'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Division</label>
                            <select name="education[masters][division]" class="rounded-md border-gray-300 w-full">
                                <option value="">Select Division</option>
                                @foreach ($educationDivisions as $division)
                                    <option value="{{ $division }}" @selected(old('education.masters.division') === $division)>{{ $division }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Passing Year</label><input name="education[masters][passing_year]" type="number" value="{{ old('education.masters.passing_year') }}" placeholder="Passing Year" class="rounded-md border-gray-300 w-full"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Course Duration (Years)</label><input name="education[masters][course_duration_years]" type="number" step="0.1" value="{{ old('education.masters.course_duration_years') }}" placeholder="Course Duration (Years)" class="rounded-md border-gray-300 w-full"></div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 mt-4">
                        <div>
                            <label for="masters_certificate" class="flex items-center gap-2 text-sm font-medium text-gray-700">Masters Certificate PDF (Optional)<a x-show="pdfPreviewUrls.masters_certificate" x-cloak :href="pdfPreviewUrls.masters_certificate" target="_blank" rel="noopener" class="inline-flex items-center rounded-md border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">View Upload</a></label>
                            <input id="masters_certificate" name="education_documents[masters][certificate]" type="file" accept="application/pdf" x-on:change="handlePdfPreview($event, 'masters_certificate')" class="mt-1 block w-full text-sm">
                        </div>
                    </div>
                </fieldset>

                <fieldset class="rounded-lg border border-gray-200 p-4">
                    <legend class="px-2 text-sm font-semibold text-gray-700">MPhil / PhD (If Applicable)</legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-2">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Subject</label><input name="education[mphil_phd][subject]" type="text" value="{{ old('education.mphil_phd.subject') }}" placeholder="Subject" class="rounded-md border-gray-300 w-full"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">University / Institute</label><input name="education[mphil_phd][institution]" type="text" value="{{ old('education.mphil_phd.institution') }}" placeholder="University / Institute" class="rounded-md border-gray-300 w-full"></div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Degree Completion Status</label>
                            <select name="education[mphil_phd][degree_completion]" class="rounded-md border-gray-300 w-full">
                                <option value="">— Select Status —</option>
                                <option value="degree_awarded" @selected(old('education.mphil_phd.degree_completion') === 'degree_awarded')>Degree Awarded</option>
                                <option value="ongoing" @selected(old('education.mphil_phd.degree_completion') === 'ongoing')>Ongoing</option>
                            </select>
                        </div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Completion Year</label><input name="education[mphil_phd][completion_year]" type="number" value="{{ old('education.mphil_phd.completion_year') }}" placeholder="Year (optional)" class="rounded-md border-gray-300 w-full"></div>
                    </div>
                </fieldset>
            </section>

            <section x-show="step === 4" x-cloak class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 space-y-6">
                <h2 class="text-lg font-semibold text-gray-900">Step 4: Job Experience</h2>

                <fieldset class="rounded-lg border border-gray-200 p-4 space-y-3">
                    <legend class="px-2 text-sm font-semibold text-gray-700">Job Experience</legend>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Job Experience (Years)</label>
                        <input name="job_experience[total_years]" type="number" min="0" step="0.1" value="{{ old('job_experience.total_years', 0) }}" placeholder="Total Job Experience (Years)" class="rounded-md border-gray-300 w-full">
                    </div>

                    <h3 class="text-sm font-semibold text-gray-700 pt-2">Current Job (If Applicable)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Job Category</label>
                            <select name="job_experience[current][job_category]" class="rounded-md border-gray-300 w-full">
                                <option value="">Select Job Category</option>
                                @foreach ($formOptions['job_categories'] as $option)
                                    <option value="{{ $option }}" @selected(old('job_experience.current.job_category') === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Organization Name</label>
                            <input name="job_experience[current][organization_name]" type="text" value="{{ old('job_experience.current.organization_name') }}" placeholder="Organization Name" class="rounded-md border-gray-300 w-full">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Current Designation / Position</label>
                            <input name="job_experience[current][designation]" type="text" value="{{ old('job_experience.current.designation') }}" placeholder="Current Designation / Position" class="rounded-md border-gray-300 w-full">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Starting Date</label>
                            <input name="job_experience[current][starting_date]" type="date" value="{{ old('job_experience.current.starting_date') }}" class="rounded-md border-gray-300 w-full">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <input name="job_experience[current][address]" type="text" value="{{ old('job_experience.current.address') }}" placeholder="Address" class="rounded-md border-gray-300 w-full">
                        </div>
                    </div>

                    <h3 class="text-sm font-semibold text-gray-700 pt-2">Previous Job (If Applicable)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Job Category</label>
                            <select name="job_experience[previous][job_category]" class="rounded-md border-gray-300 w-full">
                                <option value="">Select Job Category</option>
                                @foreach ($formOptions['job_categories'] as $option)
                                    <option value="{{ $option }}" @selected(old('job_experience.previous.job_category') === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Organization Name</label>
                            <input name="job_experience[previous][organization_name]" type="text" value="{{ old('job_experience.previous.organization_name') }}" placeholder="Organization Name" class="rounded-md border-gray-300 w-full">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Designation / Post</label>
                            <input name="job_experience[previous][designation]" type="text" value="{{ old('job_experience.previous.designation') }}" placeholder="Designation / Post" class="rounded-md border-gray-300 w-full">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Starting Date</label>
                            <input name="job_experience[previous][starting_date]" type="date" value="{{ old('job_experience.previous.starting_date') }}" class="rounded-md border-gray-300 w-full">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ending Date</label>
                            <input name="job_experience[previous][ending_date]" type="date" value="{{ old('job_experience.previous.ending_date') }}" class="rounded-md border-gray-300 w-full">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <input name="job_experience[previous][address]" type="text" value="{{ old('job_experience.previous.address') }}" placeholder="Address" class="rounded-md border-gray-300 w-full">
                        </div>
                    </div>
                </fieldset>
            </section>

            <section x-show="step === 5" x-cloak class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 space-y-6">
                <h2 class="text-lg font-semibold text-gray-900">Step 5: Subject / Course Preferences</h2>

                {{-- Program reference list --}}
                <div class="rounded-lg border border-indigo-100 bg-indigo-50 p-4">
                    <p class="text-sm font-semibold text-indigo-900 mb-2">Available Programs</p>
                    <ul class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1 text-sm text-indigo-800">
                        <li><span class="font-semibold">HRM</span> &mdash; Human Resource Management</li>
                        <li><span class="font-semibold">GPP</span> &mdash; Governance and Public Policy</li>
                        <li><span class="font-semibold">IER</span> &mdash; International Economic Relations</li>
                        <li><span class="font-semibold">PM</span> &mdash; Project Management</li>
                        <li><span class="font-semibold">PSCM</span> &mdash; Procurement and Supply Chain Management</li>
                        <li><span class="font-semibold">PPFM</span> &mdash; Public Private Financial Management</li>
                    </ul>
                </div>

                <div x-show="courseErrors.length > 0" class="rounded-lg border border-red-200 bg-red-50 p-3">
                    <p class="text-sm font-semibold text-red-800 mb-1">Please fix the following:</p>
                    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                        <template x-for="err in courseErrors" :key="err">
                            <li x-text="err"></li>
                        </template>
                    </ul>
                </div>

                <fieldset class="rounded-lg border border-gray-200 p-4">
                    <legend class="px-2 text-sm font-semibold text-gray-700">Program Choice</legend>
                    <p class="text-sm text-gray-500 mt-1 mb-3">All 6 choices are required. Each program must be selected exactly once.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <template x-for="(choice, idx) in courseChoiceFields" :key="choice.field">
                            <div>
                                <label
                                    class="block text-sm font-medium mb-1"
                                    :class="isDuplicateChoice(idx) ? 'text-red-600' : 'text-gray-700'"
                                    x-text="choice.label"
                                ></label>
                                <select
                                    :name="`course_preferences[${choice.field}]`"
                                    x-model="courseChoices[idx]"
                                    x-on:change="persistCourseChoices()"
                                    class="rounded-md border-gray-300 w-full"
                                    :class="isDuplicateChoice(idx) ? 'border-red-400 bg-red-50' : ''"
                                    required
                                >
                                    <option value="">— Select Program —</option>
                                    <template x-for="program in availableProgramsFor(idx)" :key="program">
                                        <option
                                            :value="program"
                                            x-text="program"
                                        ></option>
                                    </template>
                                </select>
                                <p x-show="isDuplicateChoice(idx)" class="mt-1 text-xs text-red-600">Already selected in another choice.</p>
                            </div>
                        </template>
                    </div>
                </fieldset>
            </section>

            <section x-show="step === 6" x-cloak class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 space-y-4">
                <h2 class="text-lg font-semibold text-gray-900">Step 6: Confirm & Submit</h2>

                <div class="rounded-lg border border-indigo-100 bg-indigo-50 p-4">
                    <p class="text-sm text-indigo-900">
                        You do not need to enter payment details now. After confirming this form, you will be redirected to the payment gateway.
                    </p>
                </div>

                <label class="flex items-start gap-2 rounded-md border border-gray-200 bg-gray-50 p-3">
                    <input name="declaration" type="checkbox" value="1" class="mt-1 rounded border-gray-300" {{ old('declaration') ? 'checked' : '' }} required>
                    <span class="text-sm text-gray-700">
                        I hereby declare that all information provided in this application is true and complete to the best of my knowledge.
                    </span>
                </label>

                <label class="flex items-start gap-2 rounded-md border border-amber-200 bg-amber-50 p-3">
                    <input name="contact_info_confirmation" type="checkbox" value="1" class="mt-1 rounded border-gray-300" {{ old('contact_info_confirmation') ? 'checked' : '' }} required>
                    <span class="text-sm text-gray-700">
                        I confirm that my name, email, and phone number are correct. I understand BIGM will use these for important mailing and administrative communication.
                    </span>
                </label>
            </section>

            {{-- Per-step client-side validation errors --}}
            <div x-show="stepErrors.length > 0" x-cloak class="rounded-lg border border-red-200 bg-red-50 p-4">
                <p class="text-sm font-semibold text-red-800 mb-1">Please complete the required fields before continuing:</p>
                <ul class="list-disc list-inside text-sm text-red-700 space-y-0.5">
                    <template x-for="err in stepErrors" :key="err">
                        <li x-text="err"></li>
                    </template>
                </ul>
            </div>

            <div
                x-show="showUploadResetToast"
                x-cloak
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-2"
                class="fixed bottom-6 right-6 z-[9999] max-w-sm rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 shadow-lg"
                role="status"
                aria-live="polite"
            >
                <p class="font-semibold" x-text="uploadResetToastMessage"></p>
            </div>

            <div class="flex items-center justify-between">
                <button
                    type="button"
                    x-show="step > 1"
                    x-on:click="previous()"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    Previous
                </button>

                <div class="ml-auto flex items-center gap-3">
                    <button
                        type="button"
                        x-show="step < totalSteps"
                        x-on:click="next()"
                        :disabled="isCheckingUniqueness"
                        class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-70"
                    >
                        <svg x-show="isCheckingUniqueness" x-cloak class="mr-2 h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-text="isCheckingUniqueness ? 'Checking...' : 'Next Step'"></span>
                    </button>

                    <button
                        type="submit"
                        x-show="step === totalSteps"
                        :disabled="isCheckingUniqueness"
                        class="inline-flex items-center rounded-md border border-transparent bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-70"
                    >
                        <svg x-show="isCheckingUniqueness" x-cloak class="mr-2 h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-text="isCheckingUniqueness ? 'Checking...' : 'Submit & Proceed to Payment'"></span>
                    </button>
                </div>
            </div>
        </form>
    </main>
    </div>

    <script>
        function applicationStepper({
            initialStep,
            totalSteps,
            districts,
            upazilas,
            presentDistrictId,
            presentUpazilaId,
            permanentDistrictId,
            permanentUpazilaId,
            initialDob,
            initialAge,
            initialSameAsPresent,
            programs,
            initialCourseChoices,
            initialPhotoUrl,
            initialSignatureUrl,
            initialPdfUrls,
            initialUploadError,
            initialHasErrors,
            initialEducationResultTypes,
        }) {
            return {
                step: initialStep,
                totalSteps,
                districts,
                upazilas,
                presentDistrictId: presentDistrictId ? String(presentDistrictId) : '',
                presentUpazilaId: presentUpazilaId ? String(presentUpazilaId) : '',
                permanentDistrictId: permanentDistrictId ? String(permanentDistrictId) : '',
                permanentUpazilaId: permanentUpazilaId ? String(permanentUpazilaId) : '',
                presentDistrictText: '',
                presentUpazilaText: '',
                permanentDistrictText: '',
                permanentUpazilaText: '',
                presentDistrictOpen: false,
                presentUpazilaOpen: false,
                permanentDistrictOpen: false,
                permanentUpazilaOpen: false,
                sameAsPresentAddress: !!initialSameAsPresent,
                ageDisplay: initialAge ?? '',
                photoPreviewUrl: initialPhotoUrl ?? null,
                signaturePreviewUrl: initialSignatureUrl ?? null,
                pdfPreviewUrls: {
                    ssc_certificate:        (initialPdfUrls && initialPdfUrls.ssc_certificate)        ? initialPdfUrls.ssc_certificate        : null,
                    hsc_certificate:        (initialPdfUrls && initialPdfUrls.hsc_certificate)        ? initialPdfUrls.hsc_certificate        : null,
                    graduation_certificate: (initialPdfUrls && initialPdfUrls.graduation_certificate) ? initialPdfUrls.graduation_certificate : null,
                    masters_certificate:    (initialPdfUrls && initialPdfUrls.masters_certificate)    ? initialPdfUrls.masters_certificate    : null,
                },
                educationResultTypes: {
                    ssc: (initialEducationResultTypes && initialEducationResultTypes.ssc) ? initialEducationResultTypes.ssc : 'numeric',
                    hsc: (initialEducationResultTypes && initialEducationResultTypes.hsc) ? initialEducationResultTypes.hsc : 'numeric',
                    graduation: (initialEducationResultTypes && initialEducationResultTypes.graduation) ? initialEducationResultTypes.graduation : 'numeric',
                    masters: (initialEducationResultTypes && initialEducationResultTypes.masters) ? initialEducationResultTypes.masters : '',
                },
                allPrograms: programs,
                showUniquenessAlert: false,
                uniquenessMessage: '',
                isCheckingUniqueness: false,
                showUploadResetToast: false,
                uploadResetToastMessage: '',
                uploadResetToastTimeoutId: null,
                courseChoicesStorageKey: 'application-course-choices-{{ $exam->ulid }}',
                courseChoices: (initialCourseChoices && initialCourseChoices.length === 6)
                    ? initialCourseChoices.map(v => v ?? '')
                    : ['', '', '', '', '', ''],
                courseChoiceFields: [
                    { field: 'first_choice',  label: 'First Choice *'  },
                    { field: 'second_choice', label: 'Second Choice *' },
                    { field: 'third_choice',  label: 'Third Choice *'  },
                    { field: 'fourth_choice', label: 'Fourth Choice *' },
                    { field: 'fifth_choice',  label: 'Fifth Choice *'  },
                    { field: 'sixth_choice',  label: 'Sixth Choice *'  },
                ],
                get courseErrors() {
                    const errors = [];
                    const filled = this.courseChoices.filter(c => c !== '');
                    if (filled.length < 6) {
                        errors.push(`All 6 course preferences must be selected (${filled.length} of 6 filled).`);
                    }
                    const unique = new Set(filled);
                    if (unique.size < filled.length) {
                        errors.push('Each program can only be selected once. Please remove duplicate selections.');
                    }
                    return errors;
                },
                isDuplicateChoice(idx) {
                    const val = this.courseChoices[idx];
                    if (!val) return false;
                    return this.courseChoices.filter(c => c === val).length > 1;
                },
                availableProgramsFor(idx) {
                    return this.allPrograms.filter((program) => this.courseChoices[idx] === program || !this.courseChoices.includes(program));
                },
                stepTitles: ['Personal', 'Address', 'Education', 'Job Experience', 'Course Choice', 'Confirm'],
                stepErrors: [],
                init() {
                    this.restoreCourseChoices();
                    this._initComboboxLabels();

                    if (initialUploadError) {
                        this.showUploadResetReminder();
                    }

                    if (initialHasErrors) {
                        this.step = 1;
                        this.highlightAllUploadFields();
                    }

                    if (initialDob) {
                        this.calculateAge(initialDob);
                    }

                    if (this.sameAsPresentAddress) {
                        this.syncPermanentFromPresent();
                    }

                    const dobInput = document.getElementById('date_of_birth');
                    if (dobInput) {
                        dobInput.addEventListener('change', (event) => {
                            this.calculateAge(event.target.value);
                        });
                    }

                    // Clear combobox highlights as soon as a value is selected
                    this.$watch('presentDistrictId',  v => { if (v) this._clearInvalid(document.getElementById('present_district_input')); });
                    this.$watch('presentUpazilaId',   v => { if (v) this._clearInvalid(document.getElementById('present_upazila_input')); });
                    this.$watch('permanentDistrictId',v => { if (v) this._clearInvalid(document.getElementById('permanent_district_input')); });
                    this.$watch('permanentUpazilaId', v => { if (v) this._clearInvalid(document.getElementById('permanent_upazila_input')); });
                },
                restoreCourseChoices() {
                    const hasInitialServerValues = this.courseChoices.some((choice) => String(choice ?? '').trim() !== '');
                    if (hasInitialServerValues) {
                        this.persistCourseChoices();
                        return;
                    }

                    try {
                        const raw = window.sessionStorage.getItem(this.courseChoicesStorageKey);
                        if (!raw) {
                            return;
                        }

                        const parsed = JSON.parse(raw);
                        if (!Array.isArray(parsed) || parsed.length !== 6) {
                            return;
                        }

                        this.courseChoices = parsed.map((choice) => String(choice ?? ''));
                    } catch (_e) {
                        // Ignore malformed storage data and continue with default choices.
                    }
                },
                persistCourseChoices() {
                    try {
                        window.sessionStorage.setItem(this.courseChoicesStorageKey, JSON.stringify(this.courseChoices));
                    } catch (_e) {
                        // Ignore storage write errors.
                    }
                },
                _initComboboxLabels() {
                    if (this.presentDistrictId) {
                        const d = this.districts.find(d => String(d.id) === this.presentDistrictId);
                        if (d) this.presentDistrictText = d.name;
                    }
                    if (this.presentUpazilaId) {
                        const u = this.upazilas.find(u => String(u.id) === this.presentUpazilaId);
                        if (u) this.presentUpazilaText = this.locationLabel(u);
                    }
                    if (this.permanentDistrictId) {
                        const d = this.districts.find(d => String(d.id) === this.permanentDistrictId);
                        if (d) this.permanentDistrictText = d.name;
                    }
                    if (this.permanentUpazilaId) {
                        const u = this.upazilas.find(u => String(u.id) === this.permanentUpazilaId);
                        if (u) this.permanentUpazilaText = this.locationLabel(u);
                    }
                },
                restoreLabel(fieldType) {
                    if (fieldType === 'presentDistrict') {
                        const d = this.districts.find(d => String(d.id) === this.presentDistrictId);
                        this.presentDistrictText = d ? d.name : '';
                    } else if (fieldType === 'presentUpazila') {
                        const u = this.upazilas.find(u => String(u.id) === this.presentUpazilaId);
                        this.presentUpazilaText = u ? this.locationLabel(u) : '';
                    } else if (fieldType === 'permanentDistrict') {
                        const d = this.districts.find(d => String(d.id) === this.permanentDistrictId);
                        this.permanentDistrictText = d ? d.name : '';
                    } else if (fieldType === 'permanentUpazila') {
                        const u = this.upazilas.find(u => String(u.id) === this.permanentUpazilaId);
                        this.permanentUpazilaText = u ? this.locationLabel(u) : '';
                    }
                },
                closeAddressDropdowns() {
                    this.presentDistrictOpen = false;
                    this.presentUpazilaOpen = false;
                    this.permanentDistrictOpen = false;
                    this.permanentUpazilaOpen = false;
                },
                toggleSameAsPresentAddress() {
                    if (this.sameAsPresentAddress) {
                        this.syncPermanentFromPresent();
                    }
                },
                syncPermanentFromPresent() {
                    this.permanentDistrictId = this.presentDistrictId;
                    this.permanentDistrictText = this.presentDistrictText;
                    this.permanentUpazilaId = this.presentUpazilaId;
                    this.permanentUpazilaText = this.presentUpazilaText;

                    this.setInputValue('permanent_address[post_office]', document.querySelector('[name="present_address[post_office]"]')?.value ?? '');
                    this.setInputValue('permanent_address[post_code]', document.querySelector('[name="present_address[post_code]"]')?.value ?? '');
                    this.setInputValue('permanent_address[address_line]', document.querySelector('[name="present_address[address_line]"]')?.value ?? '');
                },
                handleImagePreview(event, type) {
                    const file = event?.target?.files?.[0] ?? null;
                    if (!file) {
                        if (type === 'photo') this.photoPreviewUrl = null;
                        if (type === 'signature') this.signaturePreviewUrl = null;
                        return;
                    }

                    const nextUrl = URL.createObjectURL(file);
                    if (type === 'photo') {
                        if (this.photoPreviewUrl) URL.revokeObjectURL(this.photoPreviewUrl);
                        this.photoPreviewUrl = nextUrl;
                    }

                    if (type === 'signature') {
                        if (this.signaturePreviewUrl) URL.revokeObjectURL(this.signaturePreviewUrl);
                        this.signaturePreviewUrl = nextUrl;
                    }
                },
                handlePdfPreview(event, key) {
                    const file = event?.target?.files?.[0] ?? null;
                    if (this.pdfPreviewUrls[key]) {
                        URL.revokeObjectURL(this.pdfPreviewUrls[key]);
                    }
                    this.pdfPreviewUrls[key] = file ? URL.createObjectURL(file) : null;
                },
                clearUploadedFiles() {
                    const fileInputIds = [
                        'applicant_photo',
                        'signature_input',
                        'ssc_certificate',
                        'hsc_certificate',
                        'graduation_certificate',
                        'masters_certificate',
                    ];

                    fileInputIds.forEach((id) => {
                        const input = document.getElementById(id);
                        if (input) {
                            input.value = '';
                        }
                    });

                    if (this.photoPreviewUrl) {
                        URL.revokeObjectURL(this.photoPreviewUrl);
                    }
                    if (this.signaturePreviewUrl) {
                        URL.revokeObjectURL(this.signaturePreviewUrl);
                    }
                    this.photoPreviewUrl = null;
                    this.signaturePreviewUrl = null;

                    Object.keys(this.pdfPreviewUrls).forEach((key) => {
                        const url = this.pdfPreviewUrls[key];
                        if (url) {
                            URL.revokeObjectURL(url);
                        }
                        this.pdfPreviewUrls[key] = null;
                    });

                    this.highlightAllUploadFields();
                    this.showUploadResetReminder();
                },
                highlightAllUploadFields() {
                    const fileInputIds = [
                        'applicant_photo',
                        'signature_input',
                        'ssc_certificate',
                        'hsc_certificate',
                        'graduation_certificate',
                        'masters_certificate',
                    ];

                    fileInputIds.forEach((id) => {
                        const input = document.getElementById(id);
                        if (input) {
                            this._markInvalid(input);
                        }
                    });
                },
                showUploadResetReminder() {
                    this.uploadResetToastMessage = 'Validation failed: please re-upload photo, signature, and certificate files.';
                    this.showUploadResetToast = true;
                    if (this.uploadResetToastTimeoutId) {
                        clearTimeout(this.uploadResetToastTimeoutId);
                    }
                    this.uploadResetToastTimeoutId = setTimeout(() => {
                        this.showUploadResetToast = false;
                    }, 4500);
                },
                setInputValue(name, value) {
                    const input = document.querySelector(`[name="${name}"]`);
                    if (!input) {
                        return;
                    }

                    input.value = value;
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                },
                setSelectValue(name, value) {
                    const select = document.querySelector(`select[name="${name}"]`);
                    if (!select) {
                        return;
                    }

                    select.value = value;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                },
                async createDummyImageFile(width, height, fileName, label, bgColor) {
                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;

                    const ctx = canvas.getContext('2d');
                    ctx.fillStyle = bgColor;
                    ctx.fillRect(0, 0, width, height);

                    ctx.fillStyle = '#ffffff';
                    ctx.font = `${Math.max(12, Math.floor(height * 0.28))}px Arial`;
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(label, width / 2, height / 2);

                    const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/png', 0.92));

                    return new File([blob], fileName, { type: 'image/png' });
                },
                createDummyPdfFile(fileName, label) {
                    const content = `%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 300 144] /Contents 4 0 R /Resources << >> >>\nendobj\n4 0 obj\n<< /Length 58 >>\nstream\nBT /F1 12 Tf 24 96 Td (${label}) Tj ET\nendstream\nendobj\nxref\n0 5\n0000000000 65535 f \n0000000010 00000 n \n0000000060 00000 n \n0000000117 00000 n \n0000000223 00000 n \ntrailer\n<< /Size 5 /Root 1 0 R >>\nstartxref\n329\n%%EOF`;

                    return new File([content], fileName, { type: 'application/pdf' });
                },
                async attachFileToInput(inputSelector, file) {
                    const input = document.querySelector(inputSelector);
                    if (!input) {
                        return;
                    }

                    const dt = new DataTransfer();
                    dt.items.add(file);
                    input.files = dt.files;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                },
                fillEducationDefaults() {
                    const educationSelects = document.querySelectorAll('select[name^="education["]');
                    educationSelects.forEach((select) => {
                        if (select.value) {
                            return;
                        }

                        const firstValidOption = Array.from(select.options).find((opt) => opt.value !== '');
                        if (firstValidOption) {
                            select.value = firstValidOption.value;
                            select.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    });
                },
                async fillDevData() {
                    this.setInputValue('applicant_name', 'Test Applicant');
                    this.setInputValue('father_name', 'Test Father');
                    this.setInputValue('mother_name', 'Test Mother');
                    this.setSelectValue('gender', 'Male');
                    this.setInputValue('national_id_number', '1234567890');
                    this.setInputValue('mobile_number_local', '1712345678');
                    this.setInputValue('email', 'dev.applicant@example.test');

                    this.setInputValue('date_of_birth', '1998-04-15');
                    this.calculateAge('1998-04-15');

                    const presentDistrict = this.districts[0] ?? null;
                    if (presentDistrict) {
                        this.presentDistrictId = String(presentDistrict.id);
                        this.presentDistrictText = presentDistrict.name;
                        this.onDistrictChange('present');

                        const presentUpazila = this.upazilas.find((u) => String(u.parent_id) === String(presentDistrict.id));
                        if (presentUpazila) {
                            this.presentUpazilaId = String(presentUpazila.id);
                            this.presentUpazilaText = this.locationLabel(presentUpazila);
                        }
                    }

                    const permanentDistrict = this.districts[1] ?? this.districts[0] ?? null;
                    if (permanentDistrict) {
                        this.permanentDistrictId = String(permanentDistrict.id);
                        this.permanentDistrictText = permanentDistrict.name;
                        this.onDistrictChange('permanent');

                        const permanentUpazila = this.upazilas.find((u) => String(u.parent_id) === String(permanentDistrict.id));
                        if (permanentUpazila) {
                            this.permanentUpazilaId = String(permanentUpazila.id);
                            this.permanentUpazilaText = this.locationLabel(permanentUpazila);
                        }
                    }

                    this.setInputValue('present_address[post_office]', 'GPO');
                    this.setInputValue('present_address[post_code]', '1000');
                    this.setInputValue('present_address[address_line]', 'Road 1, House 10');
                    this.setInputValue('permanent_address[post_office]', 'GPO');
                    this.setInputValue('permanent_address[post_code]', '1000');
                    this.setInputValue('permanent_address[address_line]', 'Road 2, House 20');

                    this.fillEducationDefaults();
                    this.setSelectValue('education[ssc][result_type]', 'numeric');
                    this.educationResultTypes.ssc = 'numeric';
                    this.setInputValue('education[ssc][result]', '5.00');
                    this.setInputValue('education[ssc][result_scale]', '5.00');
                    this.setInputValue('education[ssc][passing_year]', '2014');
                    this.setSelectValue('education[hsc][result_type]', 'numeric');
                    this.educationResultTypes.hsc = 'numeric';
                    this.setInputValue('education[hsc][result]', '5.00');
                    this.setInputValue('education[hsc][result_scale]', '5.00');
                    this.setInputValue('education[hsc][passing_year]', '2016');
                    this.setInputValue('education[graduation][subject]', 'Computer Science');
                    this.setInputValue('education[graduation][institution]', 'Test University');
                    this.setSelectValue('education[graduation][result_type]', 'numeric');
                    this.educationResultTypes.graduation = 'numeric';
                    this.setInputValue('education[graduation][result]', '3.80');
                    this.setInputValue('education[graduation][result_scale]', '4.00');
                    this.setInputValue('education[graduation][passing_year]', '2020');
                    this.setInputValue('education[graduation][course_duration_years]', '4');
                    this.setSelectValue('education[masters][result_type]', 'numeric');
                    this.educationResultTypes.masters = 'numeric';
                    this.setInputValue('education[masters][result]', '3.70');
                    this.setInputValue('education[masters][result_scale]', '4.00');
                    this.setInputValue('job_experience[total_years]', '3.5');
                    this.setInputValue('job_experience[current][organization_name]', 'Dev Company Ltd.');
                    this.setInputValue('job_experience[current][designation]', 'Software Engineer');
                    this.setInputValue('job_experience[current][starting_date]', '2022-01-01');
                    this.setInputValue('job_experience[current][address]', 'Dhaka');

                    const jobCategorySelect = document.querySelector('select[name="job_experience[current][job_category]"]');
                    if (jobCategorySelect) {
                        const firstJobCategory = Array.from(jobCategorySelect.options).find((opt) => opt.value !== '');
                        if (firstJobCategory) {
                            jobCategorySelect.value = firstJobCategory.value;
                            jobCategorySelect.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }

                    this.courseChoices = (this.allPrograms ?? []).slice(0, 6);

                    const declaration = document.querySelector('input[name="declaration"]');
                    if (declaration) {
                        declaration.checked = true;
                        declaration.dispatchEvent(new Event('change', { bubbles: true }));
                    }

                    const contactConfirm = document.querySelector('input[name="contact_info_confirmation"]');
                    if (contactConfirm) {
                        contactConfirm.checked = true;
                        contactConfirm.dispatchEvent(new Event('change', { bubbles: true }));
                    }

                    const [photoFile, signatureFile] = await Promise.all([
                        this.createDummyImageFile(300, 300, 'dev-photo.png', 'DEV PHOTO 300x300', '#4f46e5'),
                        this.createDummyImageFile(300, 80, 'dev-signature.png', 'DEV SIGN 300x80', '#0f766e'),
                    ]);

                    await this.attachFileToInput('#applicant_photo', photoFile);
                    await this.attachFileToInput('#signature_input', signatureFile);

                    const docFiles = {
                        '#ssc_certificate': this.createDummyPdfFile('ssc-certificate.pdf', 'SSC Certificate'),
                        '#hsc_certificate': this.createDummyPdfFile('hsc-certificate.pdf', 'HSC Certificate'),
                        '#graduation_certificate': this.createDummyPdfFile('graduation-certificate.pdf', 'Graduation Certificate'),
                        '#masters_certificate': this.createDummyPdfFile('masters-certificate.pdf', 'Masters Certificate'),
                    };

                    for (const [selector, file] of Object.entries(docFiles)) {
                        await this.attachFileToInput(selector, file);
                    }

                    this.step = this.totalSteps;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },
                calculateAge(dobValue) {
                    if (!dobValue) {
                        this.ageDisplay = '';
                        return;
                    }

                    // Parse as LOCAL date (not UTC) to prevent midnight-UTC shifting
                    // the date by one day for negative-offset timezones.
                    const segs = String(dobValue).split('-').map(Number);
                    if (segs.length < 3 || segs.some(isNaN)) {
                        this.ageDisplay = '';
                        return;
                    }
                    const dob = new Date(segs[0], segs[1] - 1, segs[2]);
                    if (Number.isNaN(dob.getTime())) {
                        this.ageDisplay = '';
                        return;
                    }

                    const today = new Date();
                    let years  = today.getFullYear() - dob.getFullYear();
                    let months = today.getMonth()     - dob.getMonth();

                    // End-of-month aware birthday check:
                    // Clamp the birth-day to the last day of the current month so that
                    // e.g. being born on the 31st is treated as "birthday passed" when
                    // today is the last day of a 30-day month (April 30, etc.).
                    const lastDayOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0).getDate();
                    const effectiveBirthDay = Math.min(dob.getDate(), lastDayOfMonth);

                    if (today.getDate() < effectiveBirthDay) {
                        months--;
                    }

                    if (months < 0) {
                        years--;
                        months += 12;
                    }

                    if (years < 0) {
                        this.ageDisplay = '';
                        return;
                    }

                    this.ageDisplay = `${years} Years, ${months} Months`;
                },
                filteredDistricts(searchTerm) {
                    const needle = (searchTerm ?? '').trim().toLowerCase();
                    if (!needle) {
                        return this.districts;
                    }

                    return this.districts.filter((district) => district.name.toLowerCase().includes(needle));
                },
                filteredUpazilas(districtId, searchTerm) {
                    if (!districtId) {
                        return [];
                    }

                    const needle = (searchTerm ?? '').trim().toLowerCase();

                    return this.upazilas
                        .filter((upazila) => String(upazila.parent_id) === String(districtId))
                        .filter((upazila) => {
                            if (!needle) {
                                return true;
                            }

                            const typeLabel = upazila.type === 'thana' ? 'thana' : 'upazila';
                            return upazila.name.toLowerCase().includes(needle) || typeLabel.includes(needle);
                        });
                },
                locationLabel(location) {
                    if (!location) {
                        return '';
                    }

                    return location.name;
                },
                onDistrictChange(addressType) {
                    if (addressType === 'present') {
                        this.presentUpazilaId = '';
                        this.presentUpazilaText = '';
                        if (this.sameAsPresentAddress) {
                            this.syncPermanentFromPresent();
                        }
                    }

                    if (addressType === 'permanent') {
                        this.permanentUpazilaId = '';
                        this.permanentUpazilaText = '';
                    }
                },
                async checkUserUniqueness() {
                    const email = document.getElementById('email')?.value?.trim();
                    const phoneLocal = document.getElementById('mobile_number')?.value?.trim();

                    // Reset alert if either field is empty
                    if (!email || !phoneLocal) {
                        this.showUniquenessAlert = false;
                        this.uniquenessMessage = '';
                        return true;
                    }

                    // Normalize phone to +880 format
                    const normalizedPhone = '+880' + phoneLocal;

                    if (this.isCheckingUniqueness) {
                        return true;
                    }

                    this.isCheckingUniqueness = true;

                    try {
                        const params = new URLSearchParams({
                            email: email,
                            phone: normalizedPhone,
                        });

                        const response = await fetch(`{{ route('applications.check-uniqueness', $exam) }}?${params.toString()}`, {
                            method: 'GET',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        const data = await response.json();

                        if (data.isDuplicate) {
                            this.showUniquenessAlert = true;
                            this.uniquenessMessage = data.message;
                            return false;
                        } else {
                            this.showUniquenessAlert = false;
                            this.uniquenessMessage = '';
                            return true;
                        }
                    } catch (error) {
                        console.error('Uniqueness check failed:', error);
                        this.showUniquenessAlert = false;
                        this.uniquenessMessage = '';
                        // Fail open here; backend store() still enforces duplicate blocking.
                        return true;
                    } finally {
                        this.isCheckingUniqueness = false;
                    }
                },
                async next() {
                    const errors = this.validateStep(this.step);
                    if (errors.length > 0) {
                        this.clearUploadedFiles();
                        this.stepErrors = errors;
                        window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
                        return;
                    }

                    if (this.step === 1) {
                        const canProceed = await this.checkUserUniqueness();
                        if (!canProceed) {
                            this.clearUploadedFiles();
                            this.stepErrors = ['Duplicate paid application found for this email and mobile number.'];
                            window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
                            return;
                        }
                    }

                    this.stepErrors = [];
                    if (this.step < this.totalSteps) {
                        this.step += 1;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },
                previous() {
                    if (this.step > 1) {
                        this.step -= 1;
                        this.stepErrors = [];
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },
                async goTo(targetStep) {
                    if (targetStep < this.step) {
                        // Always allow stepping backward
                        this.step = targetStep;
                        this.stepErrors = [];
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }
                    // Validate every step from current up to (but not including) target
                    for (let s = this.step; s < targetStep; s++) {
                        const errors = this.validateStep(s);
                        if (errors.length > 0) {
                            this.clearUploadedFiles();
                            this.stepErrors = errors;
                            window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
                            return;
                        }

                        if (s === 1) {
                            const canProceed = await this.checkUserUniqueness();
                            if (!canProceed) {
                                this.clearUploadedFiles();
                                this.stepErrors = ['Duplicate paid application found for this email and mobile number.'];
                                window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
                                return;
                            }
                        }
                    }
                    this.stepErrors = [];
                    this.step = targetStep;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },
                async submitWithUniquenessCheck(event) {
                    if (this.step !== this.totalSteps) {
                        return;
                    }

                    for (let s = 1; s <= this.totalSteps; s++) {
                        const errors = this.validateStep(s);
                        if (errors.length > 0) {
                            this.clearUploadedFiles();
                            this.step = 1;
                            this.stepErrors = errors;
                            window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
                            return;
                        }
                    }

                    const canSubmit = await this.checkUserUniqueness();
                    if (!canSubmit) {
                        this.clearUploadedFiles();
                        this.step = 1;
                        this.stepErrors = ['Duplicate paid application found for this email and mobile number.'];
                        window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
                        return;
                    }

                    this.stepErrors = [];
                    event.target.submit();
                },
                validateStep(stepNum) {
                    const errors = [];

                    // Helper: check a named input is non-empty, mark red if not
                    const req = (name, label) => {
                        const el = document.querySelector(`[name="${name}"]`);
                        if (!el || !String(el.value ?? '').trim()) {
                            errors.push(`${label} is required.`);
                            this._markInvalid(el);
                        }
                    };
                    const reqSel = (name, label) => {
                        const el = document.querySelector(`[name="${name}"]`);
                        if (!el || !el.value) {
                            errors.push(`${label} is required.`);
                            this._markInvalid(el);
                        }
                    };
                    const reqCombo = (id, hasValue, label) => {
                        if (!hasValue) {
                            errors.push(`${label} is required.`);
                            this._markInvalid(document.getElementById(id));
                        }
                    };

                    if (stepNum === 1) {
                        req('applicant_name',     'Applicant Name');
                        req('father_name',        "Father's Name");
                        req('mother_name',        "Mother's Name");
                        req('national_id_number', 'National ID / Birth Reg. / Passport');
                        req('date_of_birth',      'Date of Birth');
                        req('mobile_number_local', 'Mobile Number');
                        req('email',              'Email');
                        reqSel('gender', 'Gender');

                        const photo = document.querySelector('[name="applicant_photo"]');
                        if (photo && !photo.files?.length) {
                            errors.push('Applicant Photo is required.');
                            this._markInvalid(photo);
                        }
                        const sig = document.querySelector('[name="signature"]');
                        if (sig && !sig.files?.length) {
                            errors.push('Signature is required.');
                            this._markInvalid(sig);
                        }
                    }

                    if (stepNum === 2) {
                        reqCombo('present_district_input',  this.presentDistrictId,  'Present address: District');
                        reqCombo('present_upazila_input',   this.presentUpazilaId,   'Present address: Upazila / Thana');
                        req('present_address[post_office]',  'Present address: Post Office');
                        req('present_address[post_code]',    'Present address: Post Code');
                        req('present_address[address_line]', 'Present address: Village / Road / House');
                        reqCombo('permanent_district_input', this.permanentDistrictId, 'Permanent address: District');
                        reqCombo('permanent_upazila_input',  this.permanentUpazilaId,  'Permanent address: Upazila / Thana');
                        req('permanent_address[post_office]',  'Permanent address: Post Office');
                        req('permanent_address[post_code]',    'Permanent address: Post Code');
                        req('permanent_address[address_line]', 'Permanent address: Village / Road / House');
                    }

                    if (stepNum === 3) {
                        req('education[ssc][examination]',    'SSC Examination');
                        reqSel('education[ssc][education_board]', 'SSC Education Board');
                        reqSel('education[ssc][result_type]', 'SSC Result Style');
                        if (this.educationResultTypes.ssc === 'division') {
                            reqSel('education[ssc][division]', 'SSC Division');
                        } else {
                            req('education[ssc][result_scale]', 'SSC Result Scale');
                            req('education[ssc][result]', 'SSC Result');
                            this._validateResultVsScale('education[ssc][result]', 'education[ssc][result_scale]', 'SSC', errors);
                        }
                        reqSel('education[ssc][group]',        'SSC Group');
                        req('education[ssc][passing_year]',    'SSC Passing Year');
                        this._checkDocRequired('education_documents[ssc][certificate]', 'existing_education_documents[ssc][certificate]', 'SSC Certificate PDF', errors);

                        req('education[hsc][examination]',    'HSC Examination');
                        reqSel('education[hsc][education_board]', 'HSC Education Board');
                        reqSel('education[hsc][result_type]', 'HSC Result Style');
                        if (this.educationResultTypes.hsc === 'division') {
                            reqSel('education[hsc][division]', 'HSC Division');
                        } else {
                            req('education[hsc][result_scale]', 'HSC Result Scale');
                            req('education[hsc][result]', 'HSC Result');
                            this._validateResultVsScale('education[hsc][result]', 'education[hsc][result_scale]', 'HSC', errors);
                        }
                        reqSel('education[hsc][group]',        'HSC Group');
                        req('education[hsc][passing_year]',    'HSC Passing Year');
                        this._checkDocRequired('education_documents[hsc][certificate]', 'existing_education_documents[hsc][certificate]', 'HSC Certificate PDF', errors);

                        reqSel('education[graduation][examination]',  'Graduation Examination');
                        req('education[graduation][subject]',             'Graduation Subject');
                        req('education[graduation][institution]',         'Graduation University / Institute');
                        reqSel('education[graduation][result_type]', 'Graduation Result Style');
                        if (this.educationResultTypes.graduation === 'division') {
                            reqSel('education[graduation][division]', 'Graduation Division');
                        } else {
                            req('education[graduation][result_scale]', 'Graduation Result Scale');
                            req('education[graduation][result]', 'Graduation Result');
                            this._validateResultVsScale('education[graduation][result]', 'education[graduation][result_scale]', 'Graduation', errors);
                        }
                        req('education[graduation][passing_year]',        'Graduation Passing Year');
                        req('education[graduation][course_duration_years]','Graduation Course Duration');
                        this._checkDocRequired('education_documents[graduation][certificate]', 'existing_education_documents[graduation][certificate]', 'Graduation Certificate PDF', errors);

                        if (this.educationResultTypes.masters === 'division') {
                            const mastersDivision = document.querySelector('[name="education[masters][division]"]');
                            const mastersResult = document.querySelector('[name="education[masters][result]"]')?.value;
                            const mastersScale = document.querySelector('[name="education[masters][result_scale]"]')?.value;
                            if ((mastersResult || mastersScale || mastersDivision?.value) && !mastersDivision?.value) {
                                errors.push('Masters Division is required when Division style is selected.');
                                this._markInvalid(mastersDivision);
                            }
                        } else {
                            this._validateResultVsScale('education[masters][result]', 'education[masters][result_scale]', 'Masters', errors, true);
                        }

                    }

                    if (stepNum === 4) {
                        const totalYearsInput = document.querySelector('[name="job_experience[total_years]"]');
                        if (totalYearsInput && !String(totalYearsInput.value ?? '').trim()) {
                            totalYearsInput.value = '0';
                        }
                    }

                    if (stepNum === 5) {
                        const courseErrs = this.courseErrors;
                        if (courseErrs.length > 0) {
                            errors.push(...courseErrs);
                            // Highlight empty or duplicate choice selects
                            document.querySelectorAll('select[name^="course_preferences"]').forEach((sel, idx) => {
                                if (!sel.value || this.isDuplicateChoice(idx)) this._markInvalid(sel);
                            });
                        }
                    }

                    if (stepNum === 6) {
                        const decl = document.querySelector('[name="declaration"]');
                        if (!decl?.checked) {
                            errors.push('You must accept the declaration to submit.');
                            const lbl = decl?.closest('label');
                            if (lbl) {
                                lbl.style.borderColor = '#ef4444';
                                lbl.style.boxShadow = '0 0 0 2px #fecaca';
                                decl.addEventListener('change', () => {
                                    lbl.style.borderColor = '';
                                    lbl.style.boxShadow = '';
                                }, { once: true });
                            }
                        }

                        const contactConfirm = document.querySelector('[name="contact_info_confirmation"]');
                        if (!contactConfirm?.checked) {
                            errors.push('Please confirm your name, email, and phone number before proceeding to payment.');
                            const lbl = contactConfirm?.closest('label');
                            if (lbl) {
                                lbl.style.borderColor = '#ef4444';
                                lbl.style.boxShadow = '0 0 0 2px #fecaca';
                                contactConfirm.addEventListener('change', () => {
                                    lbl.style.borderColor = '';
                                    lbl.style.boxShadow = '';
                                }, { once: true });
                            }
                        }
                    }

                    return errors;
                },
                _checkDocRequired(fileInputName, hiddenInputName, label, errors) {
                    const fileInput = document.querySelector(`[name="${fileInputName}"]`);
                    if (fileInput && !fileInput.files?.length) {
                        errors.push(`${label} is required.`);
                        this._markInvalid(fileInput);
                    }
                },
                _validateResultVsScale(resultName, scaleName, label, errors, allowPartial = false) {
                    const resultEl = document.querySelector(`[name="${resultName}"]`);
                    const scaleEl  = document.querySelector(`[name="${scaleName}"]`);
                    if (!resultEl || !scaleEl) return;
                    if (allowPartial && (!String(resultEl.value ?? '').trim() || !String(scaleEl.value ?? '').trim())) {
                        return;
                    }
                    const result = parseFloat(resultEl.value);
                    const scale  = parseFloat(scaleEl.value);
                    if (!isNaN(result) && !isNaN(scale) && result > scale) {
                        errors.push(`${label} result (${result}) cannot be greater than the result scale (${scale}).`);
                        this._markInvalid(resultEl);
                    }
                },
                _markInvalid(el) {
                    if (!el) return;
                    el.style.borderColor    = '#ef4444';
                    el.style.boxShadow      = '0 0 0 2px #fecaca';
                    el.style.backgroundColor = '#fef2f2';
                    const clear = () => this._clearInvalid(el);
                    el.addEventListener('input',  clear, { once: true });
                    el.addEventListener('change', clear, { once: true });
                },
                _clearInvalid(el) {
                    if (!el) return;
                    el.style.borderColor     = '';
                    el.style.boxShadow       = '';
                    el.style.backgroundColor = '';
                },
            };
        }
    </script>
</body>
</html>

