<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Applicant Details</h2>
            <a
                href="{{ route('admin.exams.show', ['exam' => $application->exam, 'tab' => request('tab', 'paid')]) }}"
                class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest"
            >
                Back to Applicants
            </a>
        </div>
    </x-slot>

    @php
        $extra = is_array($application->additional_info) ? $application->additional_info : [];
        $personal = data_get($extra, 'personal', []);
        $presentAddress = data_get($extra, 'present_address', []);
        $permanentAddress = data_get($extra, 'permanent_address', []);
        $education = data_get($extra, 'education', []);
        $job = data_get($extra, 'job_experience', []);
        $choices = data_get($extra, 'course_preferences', []);
        $uploads = data_get($extra, 'uploads', []);
        $educationDocuments = data_get($uploads, 'education_documents', []);

        $readonlyInputClass = 'mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 text-sm';
        $readonlyTextareaClass = 'mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 text-sm';

        $toText = static fn ($value): string => blank($value) ? 'N/A' : (string) $value;

        $formatAddress = static function (array $address) use ($toText): string {
            return implode(', ', array_filter([
                data_get($address, 'address_line'),
                data_get($address, 'post_office'),
                data_get($address, 'post_code'),
                data_get($address, 'upazila_name'),
                data_get($address, 'district_name'),
            ])) ?: 'N/A';
        };

        $programChoices = [
            'First Choice' => data_get($choices, 'first_choice'),
            'Second Choice' => data_get($choices, 'second_choice'),
            'Third Choice' => data_get($choices, 'third_choice'),
            'Fourth Choice' => data_get($choices, 'fourth_choice'),
            'Fifth Choice' => data_get($choices, 'fifth_choice'),
            'Sixth Choice' => data_get($choices, 'sixth_choice'),
        ];
    @endphp

    <div class="py-4">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <section class="bg-white shadow-sm border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900">Application Summary</h3>
                <p class="text-sm text-gray-500 mt-1">Read-only record of the submitted applicant information.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-5">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Applicant ID</label>
                        <input type="text" readonly value="{{ $application->ulid }}" class="{{ $readonlyInputClass }}">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Exam</label>
                        <input type="text" readonly value="{{ $toText($application->exam?->name) }}" class="{{ $readonlyInputClass }}">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Payment Status</label>
                        <input type="text" readonly value="{{ ucfirst($application->status) }}" class="{{ $readonlyInputClass }}">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Selection Stage</label>
                        <input type="text" readonly value="{{ str($application->selection_stage ?? 'paid')->replace('_', ' ')->title() }}" class="{{ $readonlyInputClass }}">
                    </div>
                </div>
            </section>

            <section class="bg-white shadow-sm border border-gray-200 rounded-lg p-6">
                <h3 class="text-base font-semibold text-gray-900">Personal Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                    <div><label class="block text-sm text-gray-600">Applicant Name</label><input readonly value="{{ $toText($application->applicant_name) }}" class="{{ $readonlyInputClass }}"></div>
                    <div><label class="block text-sm text-gray-600">Father's Name</label><input readonly value="{{ $toText(data_get($personal, 'father_name')) }}" class="{{ $readonlyInputClass }}"></div>
                    <div><label class="block text-sm text-gray-600">Mother's Name</label><input readonly value="{{ $toText(data_get($personal, 'mother_name')) }}" class="{{ $readonlyInputClass }}"></div>
                    <div><label class="block text-sm text-gray-600">Date of Birth</label><input readonly value="{{ $toText(data_get($personal, 'date_of_birth')) }}" class="{{ $readonlyInputClass }}"></div>
                    <div><label class="block text-sm text-gray-600">Age</label><input readonly value="{{ $toText(data_get($personal, 'age_as_of_reference')) }}" class="{{ $readonlyInputClass }}"></div>
                    <div><label class="block text-sm text-gray-600">Phone</label><input readonly value="{{ $toText($application->applicant_phone) }}" class="{{ $readonlyInputClass }}"></div>
                    <div><label class="block text-sm text-gray-600">Email</label><input readonly value="{{ $toText($application->applicant_email) }}" class="{{ $readonlyInputClass }}"></div>
                    <div><label class="block text-sm text-gray-600">National ID / Birth Reg / Passport</label><input readonly value="{{ $toText($application->applicant_id_number) }}" class="{{ $readonlyInputClass }}"></div>
                </div>
            </section>

            <section class="bg-white shadow-sm border border-gray-200 rounded-lg p-6">
                <h3 class="text-base font-semibold text-gray-900">Address Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm text-gray-600">Present Address</label>
                        <textarea readonly rows="4" class="{{ $readonlyTextareaClass }}">{{ $formatAddress($presentAddress) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Permanent Address</label>
                        <textarea readonly rows="4" class="{{ $readonlyTextareaClass }}">{{ $formatAddress($permanentAddress) }}</textarea>
                    </div>
                </div>
            </section>

            <section class="bg-white shadow-sm border border-gray-200 rounded-lg p-6">
                <h3 class="text-base font-semibold text-gray-900">Education</h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
                    @foreach (['ssc' => 'SSC', 'hsc' => 'HSC', 'graduation' => 'Graduation', 'masters' => 'Masters'] as $key => $label)
                        <div class="rounded-md border border-gray-100 p-4 bg-gray-50">
                            <p class="text-sm font-semibold text-gray-800 mb-2">{{ $label }}</p>
                            <textarea readonly rows="6" class="{{ $readonlyTextareaClass }}">{{ json_encode(data_get($education, $key, []), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: 'N/A' }}</textarea>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="bg-white shadow-sm border border-gray-200 rounded-lg p-6">
                <h3 class="text-base font-semibold text-gray-900">Career / Job Experience</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label class="block text-sm text-gray-600">Total Experience (Years)</label>
                        <input readonly value="{{ $toText(data_get($job, 'total_years')) }}" class="{{ $readonlyInputClass }}">
                    </div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm text-gray-600">Current Job</label>
                        <textarea readonly rows="6" class="{{ $readonlyTextareaClass }}">{{ json_encode(data_get($job, 'current', []), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: 'N/A' }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Previous Job</label>
                        <textarea readonly rows="6" class="{{ $readonlyTextareaClass }}">{{ json_encode(data_get($job, 'previous', []), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: 'N/A' }}</textarea>
                    </div>
                </div>
            </section>

            <section class="bg-white shadow-sm border border-gray-200 rounded-lg p-6">
                <h3 class="text-base font-semibold text-gray-900">Program Choices</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                    @foreach ($programChoices as $label => $value)
                        <div>
                            <label class="block text-sm text-gray-600">{{ $label }}</label>
                            <input readonly value="{{ $toText($value) }}" class="{{ $readonlyInputClass }}">
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="bg-white shadow-sm border border-gray-200 rounded-lg p-6">
                <h3 class="text-base font-semibold text-gray-900">Uploaded Files</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm text-gray-600">Applicant Photo</label>
                        <input readonly value="{{ $toText(data_get($uploads, 'applicant_photo')) }}" class="{{ $readonlyInputClass }}">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Signature</label>
                        <input readonly value="{{ $toText(data_get($uploads, 'signature')) }}" class="{{ $readonlyInputClass }}">
                    </div>
                </div>
                <div class="mt-5 grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @foreach (['ssc' => 'SSC', 'hsc' => 'HSC', 'graduation' => 'Graduation', 'masters' => 'Masters'] as $key => $label)
                        <div class="rounded-md border border-gray-100 p-4 bg-gray-50">
                            <p class="text-sm font-semibold text-gray-800">{{ $label }} Documents</p>
                            <div class="mt-3 space-y-3">
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Marksheet PDF</label>
                                    <input readonly value="{{ $toText(data_get($educationDocuments, $key.'.marksheet')) }}" class="{{ $readonlyInputClass }}">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Certificate PDF</label>
                                    <input readonly value="{{ $toText(data_get($educationDocuments, $key.'.certificate')) }}" class="{{ $readonlyInputClass }}">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</x-app-layout>

