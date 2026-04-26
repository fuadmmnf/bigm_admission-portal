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
<body class="bg-gray-50 text-gray-900">
    @php
        $errorKeys = $errors->keys();
        $initialStep = 1;

        foreach ($errorKeys as $errorKey) {
            if (
                str_starts_with($errorKey, 'present_address.') ||
                str_starts_with($errorKey, 'permanent_address.')
            ) {
                $initialStep = 2;
                break;
            }

            if (str_starts_with($errorKey, 'education.')) {
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

            if (in_array($errorKey, ['declaration'], true)) {
                $initialStep = 6;
                break;
            }
        }
    @endphp

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
        <div class="rounded-lg border border-indigo-100 bg-indigo-50 p-4 mb-6">
            <p class="text-sm text-indigo-900">
                Fill out all required fields from the original PDF form. After submission, you will be redirected to payment.
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4">
                <p class="font-semibold text-red-800">Please fix the highlighted errors and continue.</p>
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
                programs: @js($formOptions['programs']),
                initialCourseChoices: @js([
                    old('course_preferences.first_choice', ''),
                    old('course_preferences.second_choice', ''),
                    old('course_preferences.third_choice', ''),
                    old('course_preferences.fourth_choice', ''),
                    old('course_preferences.fifth_choice', ''),
                    old('course_preferences.sixth_choice', ''),
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
                        <label for="age_as_of_reference" class="block text-sm font-medium text-gray-700">Age (as of today) *</label>
                        <input id="age_as_of_reference" type="text" x-model="ageDisplay" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50" disabled>
                        <input name="age_as_of_reference" type="hidden" :value="ageDisplay">
                    </div>

                    <div>
                        <label for="mobile_number" class="block text-sm font-medium text-gray-700">Mobile Number *</label>
                        <input id="mobile_number" name="mobile_number" type="text" value="{{ old('mobile_number') }}" class="mt-1 block w-full rounded-md border-gray-300" required>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" class="mt-1 block w-full rounded-md border-gray-300" required>
                    </div>

                    <div>
                        <label for="applicant_photo" class="block text-sm font-medium text-gray-700">Applicant Photo * (300x300 pixels, max 1MB)</label>
                        <input id="applicant_photo" name="applicant_photo" type="file" accept="image/*" class="mt-1 block w-full text-sm" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Signature * (300x80 pixels, max 1MB)</label>
                        <input id="signature_input" name="signature" type="file" accept="image/*" class="mt-1 block w-full text-sm" required>
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
                                <input
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
                                <input
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
                                            x-on:mousedown.prevent="presentUpazilaId = String(upazila.id); presentUpazilaText = upazila.name; presentUpazilaOpen = false"
                                            x-text="upazila.name"
                                        ></button>
                                    </template>
                                    <div x-show="filteredUpazilas(presentDistrictId, presentUpazilaText).length === 0" class="px-3 py-2 text-sm text-gray-400 italic">No upazilas found</div>
                                </div>
                                <input type="hidden" name="present_address[upazila_id]" :value="presentUpazilaId">
                            </div>

                            <input name="present_address[post_office]" type="text" value="{{ old('present_address.post_office') }}" placeholder="Post Office" class="rounded-md border-gray-300" required>
                            <input name="present_address[post_code]" type="text" value="{{ old('present_address.post_code') }}" placeholder="Post Code" class="rounded-md border-gray-300" required>
                            <input name="present_address[address_line]" type="text" value="{{ old('present_address.address_line') }}" placeholder="Village/Road/House/Flat" class="rounded-md border-gray-300" required>
                        </div>
                    </fieldset>

                    <fieldset class="rounded-lg border border-gray-200 p-4">
                        <legend class="px-2 text-sm font-semibold text-gray-700">Permanent Address *</legend>
                        <div class="grid grid-cols-1 gap-3 mt-2">

                            {{-- District combobox --}}
                            <div class="relative" :class="permanentDistrictOpen ? 'pb-52' : ''">
                                <input
                                    type="text"
                                    x-model="permanentDistrictText"
                                    x-on:focus="closeAddressDropdowns(); permanentDistrictOpen = true"
                                    x-on:input="permanentDistrictId = ''; permanentDistrictOpen = true"
                                    x-on:blur="setTimeout(() => { permanentDistrictOpen = false; restoreLabel('permanentDistrict') }, 150)"
                                    placeholder="Search and select district..."
                                    autocomplete="off"
                                    class="rounded-md border-gray-300 w-full"
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
                                <input
                                    type="text"
                                    x-model="permanentUpazilaText"
                                    x-on:focus="if (permanentDistrictId) { closeAddressDropdowns(); permanentUpazilaOpen = true }"
                                    x-on:input="permanentUpazilaId = ''; permanentUpazilaOpen = true"
                                    x-on:blur="setTimeout(() => { permanentUpazilaOpen = false; restoreLabel('permanentUpazila') }, 150)"
                                    placeholder="Search and select upazila/thana..."
                                    autocomplete="off"
                                    :disabled="!permanentDistrictId"
                                    class="rounded-md border-gray-300 w-full disabled:bg-gray-100 disabled:cursor-not-allowed disabled:text-gray-400"
                                >
                                <div x-show="permanentUpazilaOpen" x-cloak class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-48 overflow-y-auto">
                                    <template x-for="upazila in filteredUpazilas(permanentDistrictId, permanentUpazilaText)" :key="upazila.id">
                                        <button
                                            type="button"
                                            class="block w-full text-left px-3 py-2 text-sm hover:bg-indigo-50"
                                            :class="permanentUpazilaId === String(upazila.id) ? 'bg-indigo-100 font-semibold text-indigo-700' : 'text-gray-700'"
                                            x-on:mousedown.prevent="permanentUpazilaId = String(upazila.id); permanentUpazilaText = upazila.name; permanentUpazilaOpen = false"
                                            x-text="upazila.name"
                                        ></button>
                                    </template>
                                    <div x-show="filteredUpazilas(permanentDistrictId, permanentUpazilaText).length === 0" class="px-3 py-2 text-sm text-gray-400 italic">No upazilas found</div>
                                </div>
                                <input type="hidden" name="permanent_address[upazila_id]" :value="permanentUpazilaId">
                            </div>

                            <input name="permanent_address[post_office]" type="text" value="{{ old('permanent_address.post_office') }}" placeholder="Post Office" class="rounded-md border-gray-300" required>
                            <input name="permanent_address[post_code]" type="text" value="{{ old('permanent_address.post_code') }}" placeholder="Post Code" class="rounded-md border-gray-300" required>
                            <input name="permanent_address[address_line]" type="text" value="{{ old('permanent_address.address_line') }}" placeholder="Village/Road/House/Flat" class="rounded-md border-gray-300" required>
                        </div>
                    </fieldset>
                </div>
            </section>

            <section x-show="step === 3" x-cloak class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 space-y-6">
                <h2 class="text-lg font-semibold text-gray-900">Step 3: Education Information</h2>

                <fieldset class="rounded-lg border border-gray-200 p-4">
                    <legend class="px-2 text-sm font-semibold text-gray-700">SSC / Equivalent *</legend>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-2">
                        <select name="education[ssc][examination]" class="rounded-md border-gray-300" required>
                            <option value="">Examination</option>
                            @foreach ($formOptions['ssc_examinations'] as $option)
                                <option value="{{ $option }}" @selected(old('education.ssc.examination') === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                        <select name="education[ssc][education_board]" class="rounded-md border-gray-300" required>
                            <option value="">Education Board</option>
                            @foreach ($formOptions['education_boards'] as $option)
                                <option value="{{ $option }}" @selected(old('education.ssc.education_board') === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                        <input name="education[ssc][result]" type="text" value="{{ old('education.ssc.result') }}" placeholder="Result (GPA/Division)" class="rounded-md border-gray-300" required>
                        <input name="education[ssc][result_scale]" type="text" value="{{ old('education.ssc.result_scale') }}" placeholder="Result Scale" class="rounded-md border-gray-300" required>
                        <select name="education[ssc][group]" class="rounded-md border-gray-300" required>
                            <option value="">Group</option>
                            @foreach ($formOptions['groups'] as $option)
                                <option value="{{ $option }}" @selected(old('education.ssc.group') === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                        <input name="education[ssc][passing_year]" type="number" value="{{ old('education.ssc.passing_year') }}" placeholder="Passing Year" class="rounded-md border-gray-300" required>
                    </div>
                </fieldset>

                <fieldset class="rounded-lg border border-gray-200 p-4">
                    <legend class="px-2 text-sm font-semibold text-gray-700">HSC / Equivalent *</legend>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-2">
                        <select name="education[hsc][examination]" class="rounded-md border-gray-300" required>
                            <option value="">Examination</option>
                            @foreach ($formOptions['hsc_examinations'] as $option)
                                <option value="{{ $option }}" @selected(old('education.hsc.examination') === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                        <select name="education[hsc][education_board]" class="rounded-md border-gray-300" required>
                            <option value="">Education Board</option>
                            @foreach ($formOptions['education_boards'] as $option)
                                <option value="{{ $option }}" @selected(old('education.hsc.education_board') === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                        <input name="education[hsc][result]" type="text" value="{{ old('education.hsc.result') }}" placeholder="Result (GPA/Division)" class="rounded-md border-gray-300" required>
                        <input name="education[hsc][result_scale]" type="text" value="{{ old('education.hsc.result_scale') }}" placeholder="Result Scale" class="rounded-md border-gray-300" required>
                        <select name="education[hsc][group]" class="rounded-md border-gray-300" required>
                            <option value="">Group</option>
                            @foreach ($formOptions['groups'] as $option)
                                <option value="{{ $option }}" @selected(old('education.hsc.group') === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                        <input name="education[hsc][passing_year]" type="number" value="{{ old('education.hsc.passing_year') }}" placeholder="Passing Year" class="rounded-md border-gray-300" required>
                    </div>
                </fieldset>

                <fieldset class="rounded-lg border border-gray-200 p-4">
                    <legend class="px-2 text-sm font-semibold text-gray-700">Graduation / Equivalent *</legend>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-2">
                        <select name="education[graduation][examination]" class="rounded-md border-gray-300" required>
                            <option value="">Examination</option>
                            @foreach ($formOptions['graduation_examinations'] as $option)
                                <option value="{{ $option }}" @selected(old('education.graduation.examination') === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                        <input name="education[graduation][subject]" type="text" value="{{ old('education.graduation.subject') }}" placeholder="Subject" class="rounded-md border-gray-300" required>
                        <input name="education[graduation][institution]" type="text" value="{{ old('education.graduation.institution') }}" placeholder="University / Institute" class="rounded-md border-gray-300" required>
                        <input name="education[graduation][result]" type="text" value="{{ old('education.graduation.result') }}" placeholder="Result (CGPA/Class/Division)" class="rounded-md border-gray-300" required>
                        <input name="education[graduation][result_scale]" type="text" value="{{ old('education.graduation.result_scale') }}" placeholder="Result Scale" class="rounded-md border-gray-300" required>
                        <input name="education[graduation][passing_year]" type="number" value="{{ old('education.graduation.passing_year') }}" placeholder="Passing Year" class="rounded-md border-gray-300" required>
                        <input name="education[graduation][course_duration_years]" type="number" step="0.1" value="{{ old('education.graduation.course_duration_years') }}" placeholder="Course Duration (Years)" class="rounded-md border-gray-300" required>
                    </div>
                </fieldset>

                <fieldset class="rounded-lg border border-gray-200 p-4">
                    <legend class="px-2 text-sm font-semibold text-gray-700">Masters / Equivalent (If Applicable)</legend>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-2">
                        <input name="education[masters][examination]" type="text" value="{{ old('education.masters.examination') }}" placeholder="Examination" class="rounded-md border-gray-300">
                        <input name="education[masters][subject]" type="text" value="{{ old('education.masters.subject') }}" placeholder="Subject" class="rounded-md border-gray-300">
                        <input name="education[masters][institution]" type="text" value="{{ old('education.masters.institution') }}" placeholder="University / Institute" class="rounded-md border-gray-300">
                        <input name="education[masters][result]" type="text" value="{{ old('education.masters.result') }}" placeholder="Result" class="rounded-md border-gray-300">
                        <input name="education[masters][result_scale]" type="text" value="{{ old('education.masters.result_scale') }}" placeholder="Result Scale" class="rounded-md border-gray-300">
                        <input name="education[masters][passing_year]" type="number" value="{{ old('education.masters.passing_year') }}" placeholder="Passing Year" class="rounded-md border-gray-300">
                        <input name="education[masters][course_duration_years]" type="number" step="0.1" value="{{ old('education.masters.course_duration_years') }}" placeholder="Course Duration (Years)" class="rounded-md border-gray-300">
                    </div>
                </fieldset>
            </section>

            <section x-show="step === 4" x-cloak class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 space-y-6">
                <h2 class="text-lg font-semibold text-gray-900">Step 4: Career / Job Experience</h2>

                <fieldset class="rounded-lg border border-gray-200 p-4 space-y-3">
                    <legend class="px-2 text-sm font-semibold text-gray-700">Job Experience</legend>

                    <input name="job_experience[total_years]" type="number" step="0.1" value="{{ old('job_experience.total_years') }}" placeholder="Total Job Experience (Years) *" class="rounded-md border-gray-300 w-full" required>

                    <h3 class="text-sm font-semibold text-gray-700 pt-2">Current Job (If Applicable)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <select name="job_experience[current][job_category]" class="rounded-md border-gray-300">
                            <option value="">Job Category</option>
                            @foreach ($formOptions['job_categories'] as $option)
                                <option value="{{ $option }}" @selected(old('job_experience.current.job_category') === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                        <input name="job_experience[current][organization_name]" type="text" value="{{ old('job_experience.current.organization_name') }}" placeholder="Organization Name" class="rounded-md border-gray-300">
                        <input name="job_experience[current][designation]" type="text" value="{{ old('job_experience.current.designation') }}" placeholder="Current Designation / Position" class="rounded-md border-gray-300">
                        <input name="job_experience[current][starting_date]" type="date" value="{{ old('job_experience.current.starting_date') }}" class="rounded-md border-gray-300">
                        <input name="job_experience[current][address]" type="text" value="{{ old('job_experience.current.address') }}" placeholder="Address" class="rounded-md border-gray-300 md:col-span-2">
                    </div>

                    <h3 class="text-sm font-semibold text-gray-700 pt-2">Previous Job (If Applicable)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <select name="job_experience[previous][job_category]" class="rounded-md border-gray-300">
                            <option value="">Job Category</option>
                            @foreach ($formOptions['job_categories'] as $option)
                                <option value="{{ $option }}" @selected(old('job_experience.previous.job_category') === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                        <input name="job_experience[previous][organization_name]" type="text" value="{{ old('job_experience.previous.organization_name') }}" placeholder="Organization Name" class="rounded-md border-gray-300">
                        <input name="job_experience[previous][designation]" type="text" value="{{ old('job_experience.previous.designation') }}" placeholder="Designation / Post" class="rounded-md border-gray-300">
                        <input name="job_experience[previous][starting_date]" type="date" value="{{ old('job_experience.previous.starting_date') }}" class="rounded-md border-gray-300">
                        <input name="job_experience[previous][ending_date]" type="date" value="{{ old('job_experience.previous.ending_date') }}" class="rounded-md border-gray-300">
                        <input name="job_experience[previous][address]" type="text" value="{{ old('job_experience.previous.address') }}" placeholder="Address" class="rounded-md border-gray-300 md:col-span-2">
                    </div>
                </fieldset>
            </section>

            <section x-show="step === 5" x-cloak class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 space-y-6">
                <h2 class="text-lg font-semibold text-gray-900">Step 5: Subject / Course Preferences</h2>

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
                                    class="rounded-md border-gray-300 w-full"
                                    :class="isDuplicateChoice(idx) ? 'border-red-400 bg-red-50' : ''"
                                    required
                                >
                                    <option value="">— Select Program —</option>
                                    <template x-for="program in allPrograms" :key="program">
                                        <option
                                            :value="program"
                                            :disabled="courseChoices.includes(program) && courseChoices[idx] !== program"
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
            </section>

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
                        class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                    >
                        Next Step
                    </button>

                    <button
                        type="submit"
                        x-show="step === totalSteps"
                        class="inline-flex items-center rounded-md border border-transparent bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                    >
                        Submit & Proceed to Payment
                    </button>
                </div>
            </div>
        </form>
    </main>

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
            programs,
            initialCourseChoices,
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
                ageDisplay: initialAge ?? '',
                allPrograms: programs,
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
                stepTitles: ['Personal', 'Address', 'Education', 'Career', 'Course Choice', 'Confirm'],
                init() {
                    this._initComboboxLabels();

                    if (initialDob) {
                        this.calculateAge(initialDob);
                    }

                    const dobInput = document.getElementById('date_of_birth');
                    if (dobInput) {
                        dobInput.addEventListener('change', (event) => {
                            this.calculateAge(event.target.value);
                        });
                    }
                },
                _initComboboxLabels() {
                    if (this.presentDistrictId) {
                        const d = this.districts.find(d => String(d.id) === this.presentDistrictId);
                        if (d) this.presentDistrictText = d.name;
                    }
                    if (this.presentUpazilaId) {
                        const u = this.upazilas.find(u => String(u.id) === this.presentUpazilaId);
                        if (u) this.presentUpazilaText = u.name;
                    }
                    if (this.permanentDistrictId) {
                        const d = this.districts.find(d => String(d.id) === this.permanentDistrictId);
                        if (d) this.permanentDistrictText = d.name;
                    }
                    if (this.permanentUpazilaId) {
                        const u = this.upazilas.find(u => String(u.id) === this.permanentUpazilaId);
                        if (u) this.permanentUpazilaText = u.name;
                    }
                },
                restoreLabel(fieldType) {
                    if (fieldType === 'presentDistrict') {
                        const d = this.districts.find(d => String(d.id) === this.presentDistrictId);
                        this.presentDistrictText = d ? d.name : '';
                    } else if (fieldType === 'presentUpazila') {
                        const u = this.upazilas.find(u => String(u.id) === this.presentUpazilaId);
                        this.presentUpazilaText = u ? u.name : '';
                    } else if (fieldType === 'permanentDistrict') {
                        const d = this.districts.find(d => String(d.id) === this.permanentDistrictId);
                        this.permanentDistrictText = d ? d.name : '';
                    } else if (fieldType === 'permanentUpazila') {
                        const u = this.upazilas.find(u => String(u.id) === this.permanentUpazilaId);
                        this.permanentUpazilaText = u ? u.name : '';
                    }
                },
                closeAddressDropdowns() {
                    this.presentDistrictOpen = false;
                    this.presentUpazilaOpen = false;
                    this.permanentDistrictOpen = false;
                    this.permanentUpazilaOpen = false;
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
                    this.setInputValue('national_id_number', '1234567890');
                    this.setInputValue('mobile_number', '01712345678');
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
                            this.presentUpazilaText = presentUpazila.name;
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
                            this.permanentUpazilaText = permanentUpazila.name;
                        }
                    }

                    this.setInputValue('present_address[post_office]', 'GPO');
                    this.setInputValue('present_address[post_code]', '1000');
                    this.setInputValue('present_address[address_line]', 'Road 1, House 10');
                    this.setInputValue('permanent_address[post_office]', 'GPO');
                    this.setInputValue('permanent_address[post_code]', '1000');
                    this.setInputValue('permanent_address[address_line]', 'Road 2, House 20');

                    this.fillEducationDefaults();
                    this.setInputValue('education[ssc][result]', '5.00');
                    this.setInputValue('education[ssc][result_scale]', '5.00');
                    this.setInputValue('education[ssc][passing_year]', '2014');
                    this.setInputValue('education[hsc][result]', '5.00');
                    this.setInputValue('education[hsc][result_scale]', '5.00');
                    this.setInputValue('education[hsc][passing_year]', '2016');
                    this.setInputValue('education[graduation][subject]', 'Computer Science');
                    this.setInputValue('education[graduation][institution]', 'Test University');
                    this.setInputValue('education[graduation][result]', '3.80');
                    this.setInputValue('education[graduation][result_scale]', '4.00');
                    this.setInputValue('education[graduation][passing_year]', '2020');
                    this.setInputValue('education[graduation][course_duration_years]', '4');

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

                    const [photoFile, signatureFile] = await Promise.all([
                        this.createDummyImageFile(300, 300, 'dev-photo.png', 'DEV PHOTO 300x300', '#4f46e5'),
                        this.createDummyImageFile(300, 80, 'dev-signature.png', 'DEV SIGN 300x80', '#0f766e'),
                    ]);

                    await this.attachFileToInput('#applicant_photo', photoFile);
                    await this.attachFileToInput('#signature_input', signatureFile);

                    this.step = this.totalSteps;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },
                calculateAge(dobValue) {
                    if (!dobValue) {
                        this.ageDisplay = '';
                        return;
                    }

                    const dob = new Date(dobValue);
                    if (Number.isNaN(dob.getTime())) {
                        this.ageDisplay = '';
                        return;
                    }

                    const today = new Date();
                    let years = today.getFullYear() - dob.getFullYear();
                    let months = today.getMonth() - dob.getMonth();

                    if (today.getDate() < dob.getDate()) {
                        months -= 1;
                    }

                    if (months < 0) {
                        years -= 1;
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
                        .filter((upazila) => !needle || upazila.name.toLowerCase().includes(needle));
                },
                onDistrictChange(addressType) {
                    if (addressType === 'present') {
                        this.presentUpazilaId = '';
                        this.presentUpazilaText = '';
                    }

                    if (addressType === 'permanent') {
                        this.permanentUpazilaId = '';
                        this.permanentUpazilaText = '';
                    }
                },
                next() {
                    if (this.step === 5 && this.courseErrors.length > 0) {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }
                    if (this.step < this.totalSteps) {
                        this.step += 1;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },
                previous() {
                    if (this.step > 1) {
                        this.step -= 1;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },
                goTo(step) {
                    this.step = step;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },
            };
        }
    </script>
</body>
</html>

