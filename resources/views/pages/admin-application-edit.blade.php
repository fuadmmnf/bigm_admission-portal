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
            @php
                $additionalInfo = is_array($additionalInfo ?? null) ? $additionalInfo : [];
                $personalInfo = data_get($additionalInfo, 'personal', []);
                $presentAddress = data_get($additionalInfo, 'present_address', []);
                $permanentAddress = data_get($additionalInfo, 'permanent_address', []);
                $educationData = data_get($additionalInfo, 'education', []);
                $educationResultTypes = $formOptions['education_result_types'] ?? ['numeric' => 'GPA/CGPA', 'division' => 'Division'];
                $educationBoards = $formOptions['education_boards'] ?? [];
                $sscExaminations = $formOptions['ssc_examinations'] ?? [];
                $hscExaminations = $formOptions['hsc_examinations'] ?? [];
                $graduationExaminations = $formOptions['graduation_examinations'] ?? [];
                $educationDivisions = $formOptions['education_divisions'] ?? [];
            @endphp

        <form
            method="POST"
            action="{{ route('admin.applications.update', $application) }}"
            class="space-y-6"
            x-data="editApplicationForm({
                districts: @js($districts),
                upazilas: @js($upazilas),
                presentDistrictId: @js(old('present_address.district_id', $application->additional_info['present_address']['district_id'] ?? '')),
                presentDistrictText: @js(old('present_address.district_name', data_get($presentAddress, 'district_name', ''))),
                presentUpazilaId: @js(old('present_address.upazila_id', $application->additional_info['present_address']['upazila_id'] ?? '')),
                presentUpazilaText: @js(old('present_address.upazila_name', data_get($presentAddress, 'upazila_name', ''))),
                permanentDistrictId: @js(old('permanent_address.district_id', $application->additional_info['permanent_address']['district_id'] ?? '')),
                permanentDistrictText: @js(old('permanent_address.district_name', data_get($permanentAddress, 'district_name', ''))),
                permanentUpazilaId: @js(old('permanent_address.upazila_id', $application->additional_info['permanent_address']['upazila_id'] ?? '')),
                permanentUpazilaText: @js(old('permanent_address.upazila_name', data_get($permanentAddress, 'upazila_name', ''))),
                initialEducationResultTypes: @js([
                    'ssc' => old('education.ssc.result_type', data_get($educationData, 'ssc.result_type', 'numeric')),
                    'hsc' => old('education.hsc.result_type', data_get($educationData, 'hsc.result_type', 'numeric')),
                    'graduation' => old('education.graduation.result_type', data_get($educationData, 'graduation.result_type', 'numeric')),
                    'masters' => old('education.masters.result_type', data_get($educationData, 'masters.result_type', '')),
                ]),
            })"
        >
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
                        <input id="father_name" name="father_name" type="text" value="{{ old('father_name', data_get($personalInfo, 'father_name', '')) }}" class="mt-1 block w-full rounded-md border-gray-300" required>
                        @error('father_name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="mother_name" class="block text-sm font-medium text-gray-700">Mother's Name *</label>
                        <input id="mother_name" name="mother_name" type="text" value="{{ old('mother_name', data_get($personalInfo, 'mother_name', '')) }}" class="mt-1 block w-full rounded-md border-gray-300" required>
                        @error('mother_name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="national_id_number" class="block text-sm font-medium text-gray-700">National ID / Birth Reg. / Passport *</label>
                        <input id="national_id_number" name="national_id_number" type="text" value="{{ old('national_id_number', $application->applicant_nid) }}" class="mt-1 block w-full rounded-md border-gray-300" required>
                        @error('national_id_number')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Date of Birth *</label>
                        <input id="date_of_birth" name="date_of_birth" type="date" value="{{ old('date_of_birth', data_get($personalInfo, 'date_of_birth', '')) }}" class="mt-1 block w-full rounded-md border-gray-300" required x-on:change="calculateAge()">
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
                                <select
                                    name="present_address[district_id]"
                                    x-model="presentDistrictId"
                                    x-on:change="onDistrictChange('present')"
                                    class="mt-1 block w-full rounded-md border-gray-300"
                                    required
                                >
                                    <option value="">Select District</option>
                                    @foreach($districts as $district)
                                        <option value="{{ $district->id }}" @selected((string) old('present_address.district_id', $application->additional_info['present_address']['district_id'] ?? '') === (string) $district->id)>
                                            {{ $district->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('present_address.district_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Upazila / Thana *</label>
                                <select
                                    name="present_address[upazila_id]"
                                    x-model="presentUpazilaId"
                                    :disabled="!presentDistrictId"
                                    class="mt-1 block w-full rounded-md border-gray-300 disabled:bg-gray-100 disabled:text-gray-400"
                                    required
                                >
                                    <option value="">Select Upazila / Thana</option>
                                    <template x-for="upazila in filteredUpazilas(presentDistrictId)" :key="upazila.id">
                                        <option :value="String(upazila.id)" x-text="locationLabel(upazila)"></option>
                                    </template>
                                </select>
                                <p class="mt-1 text-xs text-gray-500" x-show="!presentDistrictId">Select a district first.</p>
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
                                <select
                                    name="permanent_address[district_id]"
                                    x-model="permanentDistrictId"
                                    x-on:change="onDistrictChange('permanent')"
                                    class="mt-1 block w-full rounded-md border-gray-300"
                                    required
                                >
                                    <option value="">Select District</option>
                                    @foreach($districts as $district)
                                        <option value="{{ $district->id }}" @selected((string) old('permanent_address.district_id', $application->additional_info['permanent_address']['district_id'] ?? '') === (string) $district->id)>
                                            {{ $district->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('permanent_address.district_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Upazila / Thana *</label>
                                <select
                                    name="permanent_address[upazila_id]"
                                    x-model="permanentUpazilaId"
                                    :disabled="!permanentDistrictId"
                                    class="mt-1 block w-full rounded-md border-gray-300 disabled:bg-gray-100 disabled:text-gray-400"
                                    required
                                >
                                    <option value="">Select Upazila / Thana</option>
                                    <template x-for="upazila in filteredUpazilas(permanentDistrictId)" :key="upazila.id">
                                        <option :value="String(upazila.id)" x-text="locationLabel(upazila)"></option>
                                    </template>
                                </select>
                                <p class="mt-1 text-xs text-gray-500" x-show="!permanentDistrictId">Select a district first.</p>
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
                
                @php $ssc = data_get($educationData, 'ssc', []); @endphp
                <fieldset class="rounded-lg border border-gray-200 p-4 mb-4">
                    <legend class="px-2 text-sm font-semibold text-gray-700">SSC / Equivalent *</legend>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Examination *</label>
                            <select name="education[ssc][examination]" class="rounded-md border-gray-300 w-full" required>
                                <option value="">Select Examination</option>
                                @foreach ($sscExaminations as $option)
                                    <option value="{{ $option }}" @selected(old('education.ssc.examination', data_get($ssc, 'examination', '')) === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                            @error('education.ssc.examination')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Education Board *</label>
                            <select name="education[ssc][education_board]" class="rounded-md border-gray-300 w-full" required>
                                <option value="">Select Education Board</option>
                                @foreach ($educationBoards as $option)
                                    <option value="{{ $option }}" @selected(old('education.ssc.education_board', data_get($ssc, 'education_board', '')) === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                            @error('education.ssc.education_board')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Result Type *</label>
                            <select name="education[ssc][result_type]" x-model="resultTypes.ssc" class="rounded-md border-gray-300 w-full" required>
                                <option value="">Select Result Type</option>
                                @foreach ($educationResultTypes as $resultTypeKey => $resultTypeLabel)
                                    <option value="{{ $resultTypeKey }}" @selected(old('education.ssc.result_type', data_get($ssc, 'result_type', 'numeric')) === $resultTypeKey)>{{ $resultTypeLabel }}</option>
                                @endforeach
                            </select>
                            @error('education.ssc.result_type')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div x-show="resultTypes.ssc === 'numeric'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Result Scale *</label>
                            <input name="education[ssc][result_scale]" type="number" step="0.001" min="0" value="{{ old('education.ssc.result_scale', data_get($ssc, 'result_scale', '')) }}" placeholder="e.g. 5.00" class="rounded-md border-gray-300 w-full" :required="resultTypes.ssc === 'numeric'">
                            @error('education.ssc.result_scale')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div x-show="resultTypes.ssc === 'numeric'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">GPA *</label>
                            <input name="education[ssc][result]" type="number" step="0.001" min="0" value="{{ old('education.ssc.result', data_get($ssc, 'result', '')) }}" placeholder="e.g. 4.67" class="rounded-md border-gray-300 w-full" :required="resultTypes.ssc === 'numeric'">
                            @error('education.ssc.result')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div x-show="resultTypes.ssc === 'division'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Division *</label>
                            <select name="education[ssc][division]" class="rounded-md border-gray-300 w-full" :required="resultTypes.ssc === 'division'">
                                <option value="">Select Division</option>
                                @foreach ($educationDivisions as $division)
                                    <option value="{{ $division }}" @selected(old('education.ssc.division', data_get($ssc, 'division', '')) === $division)>{{ $division }}</option>
                                @endforeach
                            </select>
                            @error('education.ssc.division')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Group *</label>
                            <select name="education[ssc][group]" class="rounded-md border-gray-300 w-full" required>
                                <option value="">Select Group</option>
                                @foreach (($formOptions['groups'] ?? []) as $option)
                                    <option value="{{ $option }}" @selected(old('education.ssc.group', data_get($ssc, 'group', '')) === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                            @error('education.ssc.group')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Passing Year *</label>
                            <input name="education[ssc][passing_year]" type="text" value="{{ old('education.ssc.passing_year', data_get($ssc, 'passing_year', '')) }}" placeholder="e.g., 2019" class="rounded-md border-gray-300 w-full" required>
                            @error('education.ssc.passing_year')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Institution Name *</label>
                            <input name="education[ssc][institution_name]" type="text" value="{{ old('education.ssc.institution_name', data_get($ssc, 'institution_name', '')) }}" placeholder="Institution Name" class="rounded-md border-gray-300 w-full" required>
                            @error('education.ssc.institution_name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </fieldset>

                @php $hsc = data_get($educationData, 'hsc', []); @endphp
                <fieldset class="rounded-lg border border-gray-200 p-4 mb-4">
                    <legend class="px-2 text-sm font-semibold text-gray-700">HSC / Equivalent *</legend>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Examination *</label>
                            <select name="education[hsc][examination]" class="rounded-md border-gray-300 w-full" required>
                                <option value="">Select Examination</option>
                                @foreach ($hscExaminations as $option)
                                    <option value="{{ $option }}" @selected(old('education.hsc.examination', data_get($hsc, 'examination', '')) === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                            @error('education.hsc.examination')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Education Board *</label>
                            <select name="education[hsc][education_board]" class="rounded-md border-gray-300 w-full" required>
                                <option value="">Select Education Board</option>
                                @foreach ($educationBoards as $option)
                                    <option value="{{ $option }}" @selected(old('education.hsc.education_board', data_get($hsc, 'education_board', '')) === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                            @error('education.hsc.education_board')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Result Type *</label>
                            <select name="education[hsc][result_type]" x-model="resultTypes.hsc" class="rounded-md border-gray-300 w-full" required>
                                <option value="">Select Result Type</option>
                                @foreach ($educationResultTypes as $resultTypeKey => $resultTypeLabel)
                                    <option value="{{ $resultTypeKey }}" @selected(old('education.hsc.result_type', data_get($hsc, 'result_type', 'numeric')) === $resultTypeKey)>{{ $resultTypeLabel }}</option>
                                @endforeach
                            </select>
                            @error('education.hsc.result_type')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div x-show="resultTypes.hsc === 'numeric'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Result Scale *</label>
                            <input name="education[hsc][result_scale]" type="number" step="0.001" min="0" value="{{ old('education.hsc.result_scale', data_get($hsc, 'result_scale', '')) }}" placeholder="e.g. 5.00" class="rounded-md border-gray-300 w-full" :required="resultTypes.hsc === 'numeric'">
                            @error('education.hsc.result_scale')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div x-show="resultTypes.hsc === 'numeric'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">GPA *</label>
                            <input name="education[hsc][result]" type="number" step="0.001" min="0" value="{{ old('education.hsc.result', data_get($hsc, 'result', '')) }}" placeholder="e.g. 4.50" class="rounded-md border-gray-300 w-full" :required="resultTypes.hsc === 'numeric'">
                            @error('education.hsc.result')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div x-show="resultTypes.hsc === 'division'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Division *</label>
                            <select name="education[hsc][division]" class="rounded-md border-gray-300 w-full" :required="resultTypes.hsc === 'division'">
                                <option value="">Select Division</option>
                                @foreach ($educationDivisions as $division)
                                    <option value="{{ $division }}" @selected(old('education.hsc.division', data_get($hsc, 'division', '')) === $division)>{{ $division }}</option>
                                @endforeach
                            </select>
                            @error('education.hsc.division')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Group *</label>
                            <select name="education[hsc][group]" class="rounded-md border-gray-300 w-full" required>
                                <option value="">Select Group</option>
                                @foreach (($formOptions['groups'] ?? []) as $option)
                                    <option value="{{ $option }}" @selected(old('education.hsc.group', data_get($hsc, 'group', '')) === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                            @error('education.hsc.group')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Passing Year *</label>
                            <input name="education[hsc][passing_year]" type="text" value="{{ old('education.hsc.passing_year', data_get($hsc, 'passing_year', '')) }}" placeholder="Passing Year" class="rounded-md border-gray-300 w-full" required>
                            @error('education.hsc.passing_year')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Institution Name *</label>
                            <input name="education[hsc][institution_name]" type="text" value="{{ old('education.hsc.institution_name', data_get($hsc, 'institution_name', '')) }}" placeholder="Institution Name" class="rounded-md border-gray-300 w-full" required>
                            @error('education.hsc.institution_name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </fieldset>

                @php $graduation = data_get($educationData, 'graduation', []); @endphp
                <fieldset class="rounded-lg border border-gray-200 p-4 mb-4">
                    <legend class="px-2 text-sm font-semibold text-gray-700">Graduation / Honours *</legend>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Examination *</label>
                            <select name="education[graduation][examination]" class="rounded-md border-gray-300 w-full" required>
                                <option value="">Select Examination</option>
                                @foreach ($graduationExaminations as $option)
                                    <option value="{{ $option }}" @selected(old('education.graduation.examination', data_get($graduation, 'examination', '')) === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                            @error('education.graduation.examination')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subject *</label>
                            <input name="education[graduation][subject]" type="text" value="{{ old('education.graduation.subject', data_get($graduation, 'subject', '')) }}" placeholder="Subject" class="rounded-md border-gray-300 w-full" required>
                            @error('education.graduation.subject')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">University / Institute *</label>
                            <input name="education[graduation][institution]" type="text" value="{{ old('education.graduation.institution', data_get($graduation, 'institution', '')) }}" placeholder="University / Institute" class="rounded-md border-gray-300 w-full" required>
                            @error('education.graduation.institution')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Result Type *</label>
                            <select name="education[graduation][result_type]" x-model="resultTypes.graduation" class="rounded-md border-gray-300 w-full" required>
                                <option value="">Select Result Type</option>
                                @foreach ($educationResultTypes as $resultTypeKey => $resultTypeLabel)
                                    <option value="{{ $resultTypeKey }}" @selected(old('education.graduation.result_type', data_get($graduation, 'result_type', 'numeric')) === $resultTypeKey)>{{ $resultTypeLabel }}</option>
                                @endforeach
                            </select>
                            @error('education.graduation.result_type')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div x-show="resultTypes.graduation === 'numeric'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Result Scale *</label>
                            <input name="education[graduation][result_scale]" type="number" step="0.001" min="0" value="{{ old('education.graduation.result_scale', data_get($graduation, 'result_scale', '')) }}" placeholder="e.g. 4.00" class="rounded-md border-gray-300 w-full" :required="resultTypes.graduation === 'numeric'">
                            @error('education.graduation.result_scale')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div x-show="resultTypes.graduation === 'numeric'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">CGPA *</label>
                            <input name="education[graduation][result]" type="number" step="0.001" min="0" value="{{ old('education.graduation.result', data_get($graduation, 'result', '')) }}" placeholder="e.g. 3.75" class="rounded-md border-gray-300 w-full" :required="resultTypes.graduation === 'numeric'">
                            @error('education.graduation.result')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div x-show="resultTypes.graduation === 'division'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Division *</label>
                            <select name="education[graduation][division]" class="rounded-md border-gray-300 w-full" :required="resultTypes.graduation === 'division'">
                                <option value="">Select Division</option>
                                @foreach ($educationDivisions as $division)
                                    <option value="{{ $division }}" @selected(old('education.graduation.division', data_get($graduation, 'division', '')) === $division)>{{ $division }}</option>
                                @endforeach
                            </select>
                            @error('education.graduation.division')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Passing Year *</label>
                            <input name="education[graduation][passing_year]" type="text" value="{{ old('education.graduation.passing_year', data_get($graduation, 'passing_year', '')) }}" placeholder="Passing Year" class="rounded-md border-gray-300 w-full" required>
                            @error('education.graduation.passing_year')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Course Duration (Years) *</label>
                            <input name="education[graduation][course_duration_years]" type="number" step="0.1" value="{{ old('education.graduation.course_duration_years', data_get($graduation, 'course_duration_years', '')) }}" placeholder="Course Duration (Years)" class="rounded-md border-gray-300 w-full" required>
                            @error('education.graduation.course_duration_years')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </fieldset>

                @php $masters = data_get($educationData, 'masters', []); @endphp
                <fieldset class="rounded-lg border border-gray-200 p-4 mb-4">
                    <legend class="px-2 text-sm font-semibold text-gray-700">Masters / Equivalent (Optional)</legend>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                            <input name="education[masters][subject]" type="text" value="{{ old('education.masters.subject', data_get($masters, 'subject', '')) }}" placeholder="Subject" class="rounded-md border-gray-300 w-full">
                            @error('education.masters.subject')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">University / Institute</label>
                            <input name="education[masters][institution]" type="text" value="{{ old('education.masters.institution', data_get($masters, 'institution', '')) }}" placeholder="University / Institute" class="rounded-md border-gray-300 w-full">
                            @error('education.masters.institution')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Result Type</label>
                            <select name="education[masters][result_type]" x-model="resultTypes.masters" class="rounded-md border-gray-300 w-full">
                                <option value="">Select Result Type</option>
                                @foreach ($educationResultTypes as $resultTypeKey => $resultTypeLabel)
                                    <option value="{{ $resultTypeKey }}" @selected(old('education.masters.result_type', data_get($masters, 'result_type', '')) === $resultTypeKey)>{{ $resultTypeLabel }}</option>
                                @endforeach
                            </select>
                            @error('education.masters.result_type')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div x-show="resultTypes.masters === 'numeric'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Result Scale</label>
                            <input name="education[masters][result_scale]" type="number" step="0.001" min="0" value="{{ old('education.masters.result_scale', data_get($masters, 'result_scale', '')) }}" placeholder="e.g. 4.00" class="rounded-md border-gray-300 w-full">
                            @error('education.masters.result_scale')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div x-show="resultTypes.masters === 'numeric'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Result</label>
                            <input name="education[masters][result]" type="number" step="0.001" min="0" value="{{ old('education.masters.result', data_get($masters, 'result', '')) }}" placeholder="Result" class="rounded-md border-gray-300 w-full">
                            @error('education.masters.result')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div x-show="resultTypes.masters === 'division'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Division</label>
                            <select name="education[masters][division]" class="rounded-md border-gray-300 w-full">
                                <option value="">Select Division</option>
                                @foreach ($educationDivisions as $division)
                                    <option value="{{ $division }}" @selected(old('education.masters.division', data_get($masters, 'division', '')) === $division)>{{ $division }}</option>
                                @endforeach
                            </select>
                            @error('education.masters.division')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Passing Year</label>
                            <input name="education[masters][passing_year]" type="number" value="{{ old('education.masters.passing_year', data_get($masters, 'passing_year', '')) }}" placeholder="Passing Year" class="rounded-md border-gray-300 w-full">
                            @error('education.masters.passing_year')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Course Duration (Years)</label>
                            <input name="education[masters][course_duration_years]" type="number" step="0.1" value="{{ old('education.masters.course_duration_years', data_get($masters, 'course_duration_years', '')) }}" placeholder="Course Duration (Years)" class="rounded-md border-gray-300 w-full">
                            @error('education.masters.course_duration_years')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </fieldset>

                @php $mphilPhd = data_get($educationData, 'mphil_phd', []); @endphp
                <fieldset class="rounded-lg border border-gray-200 p-4">
                    <legend class="px-2 text-sm font-semibold text-gray-700">MPhil / PhD (If Applicable)</legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                            <input name="education[mphil_phd][subject]" type="text" value="{{ old('education.mphil_phd.subject', data_get($mphilPhd, 'subject', '')) }}" placeholder="Subject" class="rounded-md border-gray-300 w-full">
                            @error('education.mphil_phd.subject')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">University / Institute</label>
                            <input name="education[mphil_phd][institution]" type="text" value="{{ old('education.mphil_phd.institution', data_get($mphilPhd, 'institution', '')) }}" placeholder="University / Institute" class="rounded-md border-gray-300 w-full">
                            @error('education.mphil_phd.institution')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Degree Completion Status</label>
                            <select name="education[mphil_phd][degree_completion]" class="rounded-md border-gray-300 w-full">
                                <option value="">— Select Status —</option>
                                <option value="degree_awarded" @selected(old('education.mphil_phd.degree_completion', data_get($mphilPhd, 'degree_completion', '')) === 'degree_awarded')>Degree Awarded</option>
                                <option value="ongoing" @selected(old('education.mphil_phd.degree_completion', data_get($mphilPhd, 'degree_completion', '')) === 'ongoing')>Ongoing</option>
                            </select>
                            @error('education.mphil_phd.degree_completion')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Completion Year</label>
                            <input name="education[mphil_phd][completion_year]" type="number" value="{{ old('education.mphil_phd.completion_year', data_get($mphilPhd, 'completion_year', '')) }}" placeholder="Year (optional)" class="rounded-md border-gray-300 w-full">
                            @error('education.mphil_phd.completion_year')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </fieldset>
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
function editApplicationForm({
    districts = [],
    upazilas = [],
    presentDistrictId = '',
    presentDistrictText = '',
    presentUpazilaId = '',
    presentUpazilaText = '',
    permanentDistrictId = '',
    permanentDistrictText = '',
    permanentUpazilaId = '',
    permanentUpazilaText = '',
    initialEducationResultTypes = {},
} = {}) {
    return {
        districts,
        upazilas,
        presentDistrictId: presentDistrictId ? String(presentDistrictId) : '',
        presentDistrictText,
        presentUpazilaId: presentUpazilaId ? String(presentUpazilaId) : '',
        presentUpazilaText,
        permanentDistrictId: permanentDistrictId ? String(permanentDistrictId) : '',
        permanentDistrictText,
        permanentUpazilaId: permanentUpazilaId ? String(permanentUpazilaId) : '',
        permanentUpazilaText,
        resultTypes: {
            ssc: initialEducationResultTypes.ssc ?? 'numeric',
            hsc: initialEducationResultTypes.hsc ?? 'numeric',
            graduation: initialEducationResultTypes.graduation ?? 'numeric',
            masters: initialEducationResultTypes.masters ?? '',
            mphil_phd: 'numeric',
        },
        init() {
            this.presentDistrictId = this.presentDistrictId ? String(this.presentDistrictId) : '';
            this.presentUpazilaId = this.presentUpazilaId ? String(this.presentUpazilaId) : '';
            this.permanentDistrictId = this.permanentDistrictId ? String(this.permanentDistrictId) : '';
            this.permanentUpazilaId = this.permanentUpazilaId ? String(this.permanentUpazilaId) : '';
            if (! this.presentDistrictText && this.presentDistrictId) {
                const district = this.districts.find((item) => String(item.id) === this.presentDistrictId);
                if (district) {
                    this.presentDistrictText = district.name;
                }
            }
            if (! this.permanentDistrictText && this.permanentDistrictId) {
                const district = this.districts.find((item) => String(item.id) === this.permanentDistrictId);
                if (district) {
                    this.permanentDistrictText = district.name;
                }
            }
            if (! this.presentUpazilaText && this.presentUpazilaId) {
                const upazila = this.upazilas.find((item) => String(item.id) === this.presentUpazilaId);
                if (upazila) {
                    this.presentUpazilaText = this.locationLabel(upazila);
                }
            }
            if (! this.permanentUpazilaText && this.permanentUpazilaId) {
                const upazila = this.upazilas.find((item) => String(item.id) === this.permanentUpazilaId);
                if (upazila) {
                    this.permanentUpazilaText = this.locationLabel(upazila);
                }
            }
        },
        updateResultFields(level) {
            const select = document.querySelector(`select[name="education[${level}][result_type]"]`);
            if (select) {
                this.resultTypes[level] = select.value;
            }
        },
        filteredUpazilas(districtId) {
            if (!districtId) {
                return [];
            }

            return this.upazilas.filter((upazila) => String(upazila.parent_id) === String(districtId));
        },
        locationLabel(location) {
            if (!location) {
                return '';
            }

            return location.type === 'thana' ? `${location.name} (Thana)` : location.name;
        },
        onDistrictChange(addressType) {
            if (addressType === 'present') {
                this.presentUpazilaId = '';
                this.presentUpazilaText = '';
                return;
            }

            this.permanentUpazilaId = '';
            this.permanentUpazilaText = '';
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
