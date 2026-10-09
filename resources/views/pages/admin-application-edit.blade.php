<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Application</h2>
            <a
                href="{{ route('admin.applications.show', $application) }}"
                class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50"
            >
                Back to Application
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

        <form method="POST" action="{{ route('admin.applications.update', $application) }}" class="space-y-6" x-data="editApplicationForm()">
            @csrf
            @method('PATCH')

            <!-- Personal Information Section -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Personal Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="applicant_name" class="block text-sm font-medium text-gray-700">Applicant's Name *</label>
                        <input id="applicant_name" name="applicant_name" type="text" value="{{ old('applicant_name', $application->applicant_name) }}" class="mt-1 block w-full rounded-md border-gray-300" required>
                        @error('applicant_name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="father_name" class="block text-sm font-medium text-gray-700">Father's Name *</label>
                        <input id="father_name" name="father_name" type="text" value="{{ old('father_name', $application->additional_info['personal']['father_name'] ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300" required>
                        @error('father_name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="mother_name" class="block text-sm font-medium text-gray-700">Mother's Name *</label>
                        <input id="mother_name" name="mother_name" type="text" value="{{ old('mother_name', $application->additional_info['personal']['mother_name'] ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300" required>
                        @error('mother_name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="national_id_number" class="block text-sm font-medium text-gray-700">National ID / Birth Reg. / Passport *</label>
                        <input id="national_id_number" name="national_id_number" type="text" value="{{ old('national_id_number', $application->national_id_number) }}" class="mt-1 block w-full rounded-md border-gray-300" required>
                        @error('national_id_number')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Date of Birth *</label>
                        <input id="date_of_birth" name="date_of_birth" type="date" value="{{ old('date_of_birth', $application->additional_info['personal']['date_of_birth'] ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300" required x-on:change="calculateAge()">
                        @error('date_of_birth')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700">Gender *</label>
                        <select id="gender" name="gender" class="mt-1 block w-full rounded-md border-gray-300" required>
                            <option value="">Select Gender</option>
                            <option value="Male" @selected(old('gender', $application->gender) === 'Male')>Male</option>
                            <option value="Female" @selected(old('gender', $application->gender) === 'Female')>Female</option>
                            <option value="Other" @selected(old('gender', $application->gender) === 'Other')>Other</option>
                        </select>
                        @error('gender')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="mobile_number" class="block text-sm font-medium text-gray-700">Mobile Number *</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <span class="inline-flex items-center rounded-l-md border border-r-0 border-gray-300 bg-gray-100 px-3 text-sm text-gray-700">+880</span>
                            <input id="mobile_number" name="mobile_number_local" type="text" inputmode="numeric" pattern="1[0-9]{9}" maxlength="10" value="{{ old('mobile_number_local', $mobileLocal ?? '') }}" x-on:input="$event.target.value = ($event.target.value || '').replace(/\D+/g, '').slice(0, 10)" class="block w-full rounded-r-md border-gray-300" placeholder="1XXXXXXXXX" required>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Enter 10 digits only. Country code +880 is added automatically.</p>
                        @error('mobile_number_local')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $application->applicant_email) }}" class="mt-1 block w-full rounded-md border-gray-300" required>
                        @error('email')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Address Information Section -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Address Information</h3>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Present Address -->
                    <fieldset class="rounded-lg border border-gray-200 p-4">
                        <legend class="px-2 text-sm font-semibold text-gray-700">Present Address *</legend>
                        <div class="grid grid-cols-1 gap-3 mt-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">District *</label>
                                <input type="text" name="present_address[district_id]" value="{{ old('present_address.district_id', $application->additional_info['present_address']['district_id'] ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300" placeholder="District ID" required>
                                @error('present_address.district_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Upazila / Thana *</label>
                                <input type="text" name="present_address[upazila_id]" value="{{ old('present_address.upazila_id', $application->additional_info['present_address']['upazila_id'] ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300" placeholder="Upazila ID" required>
                                @error('present_address.upazila_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Post Office *</label>
                                <input name="present_address[post_office]" type="text" value="{{ old('present_address.post_office', $application->additional_info['present_address']['post_office'] ?? '') }}" placeholder="Post Office" class="rounded-md border-gray-300 w-full" required>
                                @error('present_address.post_office')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Post Code *</label>
                                <input name="present_address[post_code]" type="text" value="{{ old('present_address.post_code', $application->additional_info['present_address']['post_code'] ?? '') }}" placeholder="Post Code" class="rounded-md border-gray-300 w-full" required>
                                @error('present_address.post_code')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Village / Road / House / Flat *</label>
                                <input name="present_address[address_line]" type="text" value="{{ old('present_address.address_line', $application->additional_info['present_address']['address_line'] ?? '') }}" placeholder="Village/Road/House/Flat" class="rounded-md border-gray-300 w-full" required>
                                @error('present_address.address_line')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </fieldset>

                    <!-- Permanent Address -->
                    <fieldset class="rounded-lg border border-gray-200 p-4">
                        <legend class="px-2 text-sm font-semibold text-gray-700">Permanent Address *</legend>
                        <div class="grid grid-cols-1 gap-3 mt-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">District *</label>
                                <input type="text" name="permanent_address[district_id]" value="{{ old('permanent_address.district_id', $application->additional_info['permanent_address']['district_id'] ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300" placeholder="District ID" required>
                                @error('permanent_address.district_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Upazila / Thana *</label>
                                <input type="text" name="permanent_address[upazila_id]" value="{{ old('permanent_address.upazila_id', $application->additional_info['permanent_address']['upazila_id'] ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300" placeholder="Upazila ID" required>
                                @error('permanent_address.upazila_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Post Office *</label>
                                <input name="permanent_address[post_office]" type="text" value="{{ old('permanent_address.post_office', $application->additional_info['permanent_address']['post_office'] ?? '') }}" placeholder="Post Office" class="rounded-md border-gray-300 w-full" required>
                                @error('permanent_address.post_office')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Post Code *</label>
                                <input name="permanent_address[post_code]" type="text" value="{{ old('permanent_address.post_code', $application->additional_info['permanent_address']['post_code'] ?? '') }}" placeholder="Post Code" class="rounded-md border-gray-300 w-full" required>
                                @error('permanent_address.post_code')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Village / Road / House / Flat *</label>
                                <input name="permanent_address[address_line]" type="text" value="{{ old('permanent_address.address_line', $application->additional_info['permanent_address']['address_line'] ?? '') }}" placeholder="Village/Road/House/Flat" class="rounded-md border-gray-300 w-full" required>
                                @error('permanent_address.address_line')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>

            <!-- Education Section -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Education Information</h3>
                
                @php
                    $educationLevels = ['ssc', 'hsc', 'graduation', 'masters', 'mphil_phd'];
                    $educationLabels = [
                        'ssc' => 'SSC / Equivalent',
                        'hsc' => 'HSC / Equivalent',
                        'graduation' => 'Graduation (Bachelor)',
                        'masters' => 'Masters',
                        'mphil_phd' => 'MPhil / PhD',
                    ];
                    $requiredLevels = ['ssc', 'hsc', 'graduation'];
                @endphp

                @foreach($educationLevels as $level)
                    @php
                        $eduData = $application->additional_info['education'][$level] ?? [];
                        $isRequired = in_array($level, $requiredLevels);
                    @endphp
                    <fieldset class="rounded-lg border border-gray-200 p-4 mb-4">
                        <legend class="px-2 text-sm font-semibold text-gray-700">{{ $educationLabels[$level] }} {{ $isRequired ? '*' : '(Optional)' }}</legend>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Examination {{ $isRequired ? '*' : '' }}</label>
                                <select name="education[{{ $level }}][examination]" class="rounded-md border-gray-300 w-full" {{ $isRequired ? 'required' : '' }}>
                                    <option value="">Select Examination</option>
                                    <option value="SSC" @selected(($eduData['examination'] ?? '') === 'SSC')>SSC</option>
                                    <option value="Dakhil" @selected(($eduData['examination'] ?? '') === 'Dakhil')>Dakhil</option>
                                </select>
                                @error("education.{$level}.examination")<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Education Board {{ $isRequired ? '*' : '' }}</label>
                                <select name="education[{{ $level }}][education_board]" class="rounded-md border-gray-300 w-full" {{ $isRequired ? 'required' : '' }}>
                                    <option value="">Select Education Board</option>
                                    <option value="Dhaka" @selected(($eduData['education_board'] ?? '') === 'Dhaka')>Dhaka</option>
                                    <option value="Chittagong" @selected(($eduData['education_board'] ?? '') === 'Chittagong')>Chittagong</option>
                                    <option value="Rajshahi" @selected(($eduData['education_board'] ?? '') === 'Rajshahi')>Rajshahi</option>
                                </select>
                                @error("education.{$level}.education_board")<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Result Type {{ $isRequired ? '*' : '' }}</label>
                                <select name="education[{{ $level }}][result_type]" class="rounded-md border-gray-300 w-full" x-on:change="updateResultFields('{{ $level }}')" {{ $isRequired ? 'required' : '' }}>
                                    <option value="">Select Result Type</option>
                                    <option value="numeric" @selected(($eduData['result_type'] ?? '') === 'numeric')>GPA/CGPA</option>
                                    <option value="division" @selected(($eduData['result_type'] ?? '') === 'division')>Division</option>
                                </select>
                                @error("education.{$level}.result_type")<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>

                            @php $resultType = $eduData['result_type'] ?? null; @endphp

                            @if($resultType === 'numeric' || !$resultType)
                            <div x-show="resultTypes['{{ $level }}'] === 'numeric'" x-cloak>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Result (GPA/CGPA)</label>
                                <input name="education[{{ $level }}][result]" type="text" value="{{ old("education.{$level}.result", $eduData['result'] ?? '') }}" placeholder="e.g., 4.0" class="rounded-md border-gray-300 w-full">
                                @error("education.{$level}.result")<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div x-show="resultTypes['{{ $level }}'] === 'numeric'" x-cloak>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Result Scale (e.g., out of 4)</label>
                                <input name="education[{{ $level }}][result_scale]" type="text" value="{{ old("education.{$level}.result_scale", $eduData['result_scale'] ?? '') }}" placeholder="e.g., 4" class="rounded-md border-gray-300 w-full">
                                @error("education.{$level}.result_scale")<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                            @endif

                            @if($resultType === 'division' || !$resultType)
                            <div x-show="resultTypes['{{ $level }}'] === 'division'" x-cloak>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Division</label>
                                <select name="education[{{ $level }}][division]" class="rounded-md border-gray-300 w-full">
                                    <option value="">Select Division</option>
                                    <option value="First Division" @selected(($eduData['division'] ?? '') === 'First Division')>First Division</option>
                                    <option value="Second Division" @selected(($eduData['division'] ?? '') === 'Second Division')>Second Division</option>
                                    <option value="Third Division" @selected(($eduData['division'] ?? '') === 'Third Division')>Third Division</option>
                                </select>
                                @error("education.{$level}.division")<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                            @endif

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Passing Year {{ $isRequired ? '*' : '' }}</label>
                                <input name="education[{{ $level }}][passing_year]" type="text" value="{{ old("education.{$level}.passing_year", $eduData['passing_year'] ?? '') }}" placeholder="e.g., 2019" class="rounded-md border-gray-300 w-full" {{ $isRequired ? 'required' : '' }}>
                                @error("education.{$level}.passing_year")<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Institution Name {{ $isRequired ? '*' : '' }}</label>
                                <input name="education[{{ $level }}][institution_name]" type="text" value="{{ old("education.{$level}.institution_name", $eduData['institution_name'] ?? '') }}" placeholder="Institution Name" class="rounded-md border-gray-300 w-full" {{ $isRequired ? 'required' : '' }}>
                                @error("education.{$level}.institution_name")<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </fieldset>
                @endforeach
            </div>

            <!-- Job Experience Section -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Job Experience (Optional)</h3>
                
                @php $jobExperience = $application->additional_info['job_experience'] ?? []; @endphp

                <fieldset class="rounded-lg border border-gray-200 p-4 mb-4">
                    <legend class="px-2 text-sm font-semibold text-gray-700">Current Job</legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Job Category</label>
                            <input name="job_experience[current][job_category]" type="text" value="{{ old('job_experience.current.job_category', $jobExperience['current']['job_category'] ?? '') }}" placeholder="e.g., Management, Technical" class="rounded-md border-gray-300 w-full">
                            @error('job_experience.current.job_category')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Designation</label>
                            <input name="job_experience[current][designation]" type="text" value="{{ old('job_experience.current.designation', $jobExperience['current']['designation'] ?? '') }}" placeholder="Job Title" class="rounded-md border-gray-300 w-full">
                            @error('job_experience.current.designation')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Organization</label>
                            <input name="job_experience[current][organization_name]" type="text" value="{{ old('job_experience.current.organization_name', $jobExperience['current']['organization_name'] ?? '') }}" placeholder="Company/Organization Name" class="rounded-md border-gray-300 w-full">
                            @error('job_experience.current.organization_name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <input name="job_experience[current][address]" type="text" value="{{ old('job_experience.current.address', $jobExperience['current']['address'] ?? '') }}" placeholder="Office Address" class="rounded-md border-gray-300 w-full">
                            @error('job_experience.current.address')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Starting Date</label>
                            <input name="job_experience[current][starting_date]" type="date" value="{{ old('job_experience.current.starting_date', $jobExperience['current']['starting_date'] ?? '') }}" class="rounded-md border-gray-300 w-full">
                            @error('job_experience.current.starting_date')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </fieldset>

                <fieldset class="rounded-lg border border-gray-200 p-4">
                    <legend class="px-2 text-sm font-semibold text-gray-700">Previous Job</legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Job Category</label>
                            <input name="job_experience[previous][job_category]" type="text" value="{{ old('job_experience.previous.job_category', $jobExperience['previous']['job_category'] ?? '') }}" placeholder="e.g., Management, Technical" class="rounded-md border-gray-300 w-full">
                            @error('job_experience.previous.job_category')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Designation</label>
                            <input name="job_experience[previous][designation]" type="text" value="{{ old('job_experience.previous.designation', $jobExperience['previous']['designation'] ?? '') }}" placeholder="Job Title" class="rounded-md border-gray-300 w-full">
                            @error('job_experience.previous.designation')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Organization</label>
                            <input name="job_experience[previous][organization_name]" type="text" value="{{ old('job_experience.previous.organization_name', $jobExperience['previous']['organization_name'] ?? '') }}" placeholder="Company/Organization Name" class="rounded-md border-gray-300 w-full">
                            @error('job_experience.previous.organization_name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <input name="job_experience[previous][address]" type="text" value="{{ old('job_experience.previous.address', $jobExperience['previous']['address'] ?? '') }}" placeholder="Office Address" class="rounded-md border-gray-300 w-full">
                            @error('job_experience.previous.address')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Starting Date</label>
                            <input name="job_experience[previous][starting_date]" type="date" value="{{ old('job_experience.previous.starting_date', $jobExperience['previous']['starting_date'] ?? '') }}" class="rounded-md border-gray-300 w-full">
                            @error('job_experience.previous.starting_date')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ending Date</label>
                            <input name="job_experience[previous][ending_date]" type="date" value="{{ old('job_experience.previous.ending_date', $jobExperience['previous']['ending_date'] ?? '') }}" class="rounded-md border-gray-300 w-full">
                            @error('job_experience.previous.ending_date')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </fieldset>
            </div>

            <!-- Course Preferences Section -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Course Preferences (Select 6) *</h3>
                
                @php
                    $coursePreferences = $application->additional_info['course_preferences'] ?? [];
                    $programs = config('applicant_form.programs', []);
                    $choiceLabels = ['first_choice' => 'Preference 1', 'second_choice' => 'Preference 2', 'third_choice' => 'Preference 3', 'fourth_choice' => 'Preference 4', 'fifth_choice' => 'Preference 5', 'sixth_choice' => 'Preference 6'];
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($choiceLabels as $choiceField => $choiceLabel)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $choiceLabel }} *</label>
                            <select name="course_preferences[{{ $choiceField }}]" class="rounded-md border-gray-300 w-full" required>
                                <option value="">Select Course</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program }}" @selected(($coursePreferences[$choiceField] ?? null) === $program)>{{ $program }}</option>
                                @endforeach
                            </select>
                            @error("course_preferences.{$choiceField}")<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex gap-3 justify-end">
                <a href="{{ route('admin.applications.show', $application) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    Save Changes
                </button>
            </div>
        </form>
        </div>
    </div>

<script>
function editApplicationForm() {
    return {
        resultTypes: {
            ssc: 'numeric',
            hsc: 'numeric',
            graduation: 'numeric',
            masters: 'numeric',
            mphil_phd: 'numeric',
        },
        updateResultFields(level) {
            const select = document.querySelector(`select[name="education[${level}][result_type]"]`);
            if (select) {
                this.resultTypes[level] = select.value;
            }
        },
        calculateAge() {
            const dobInput = document.getElementById('date_of_birth');
            if (!dobInput.value) return;
            
            const dob = new Date(dobInput.value);
            const today = new Date();
            let years = today.getFullYear() - dob.getFullYear();
            let months = today.getMonth() - dob.getMonth();
            
            if (months < 0) {
                years--;
                months += 12;
            }
        },
    };
}
</script>
        </div>
    </div>
</x-app-layout>
