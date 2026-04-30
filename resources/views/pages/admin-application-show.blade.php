<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
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

        $toText = static fn ($value): string => blank($value) ? 'N/A' : (string) $value;
        $publicUrl = static function ($path): ?string {
            if (blank($path)) {
                return null;
            }

            $normalized = ltrim((string) $path, '/');
            if (str_starts_with($normalized, 'public/')) {
                $normalized = substr($normalized, 7);
            }

            return asset('storage/'.$normalized);
        };

        $photoPath = data_get($uploads, 'applicant_photo');
        $signaturePath = data_get($uploads, 'signature');
        $photoUrl = $publicUrl($photoPath);
        $signatureUrl = $publicUrl($signaturePath);

        $formatAddress = static function (array $address): string {
            return implode(', ', array_filter([
                data_get($address, 'address_line'),
                data_get($address, 'post_office'),
                data_get($address, 'post_code'),
                data_get($address, 'upazila_name'),
                data_get($address, 'district_name'),
            ])) ?: 'N/A';
        };

        $educationLabels = ['ssc' => 'SSC', 'hsc' => 'HSC', 'graduation' => 'Graduation', 'masters' => 'Masters'];
        $programChoices = [
            '1st Choice' => data_get($choices, 'first_choice'),
            '2nd Choice' => data_get($choices, 'second_choice'),
            '3rd Choice' => data_get($choices, 'third_choice'),
            '4th Choice' => data_get($choices, 'fourth_choice'),
            '5th Choice' => data_get($choices, 'fifth_choice'),
            '6th Choice' => data_get($choices, 'sixth_choice'),
        ];
    @endphp

    <div class="py-5">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <section class="bg-white shadow-sm border border-gray-200 rounded-lg p-6">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">{{ $toText($application->applicant_name) }}</h3>
                        <p class="text-sm text-gray-500 mt-1">Application ID: {{ $application->ulid }}</p>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2 text-sm">
                            <p><span class="font-semibold text-gray-700">Exam:</span> {{ $toText($application->exam?->name) }}</p>
                            <p><span class="font-semibold text-gray-700">Gender:</span> {{ $toText($application->gender ?? data_get($personal, 'gender')) }}</p>
                            <p><span class="font-semibold text-gray-700">Date of Birth:</span> {{ $toText(data_get($personal, 'date_of_birth')) }}</p>
                            <p><span class="font-semibold text-gray-700">Age:</span> {{ $toText(data_get($personal, 'age_as_of_reference')) }}</p>
                            <p><span class="font-semibold text-gray-700">Phone:</span> {{ $toText($application->applicant_phone) }}</p>
                            <p><span class="font-semibold text-gray-700">Email:</span> {{ $toText($application->applicant_email) }}</p>
                            <p><span class="font-semibold text-gray-700">ID Number:</span> {{ $toText($application->applicant_id_number) }}</p>
                            <p><span class="font-semibold text-gray-700">Status:</span> {{ ucfirst($application->status) }} / {{ str($application->selection_stage ?? 'paid')->replace('_', ' ')->title() }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 w-full lg:w-auto">
                        <div class="rounded-md border border-gray-200 p-2 bg-gray-50 text-center">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Photo</p>
                            @if ($photoUrl)
                                <img src="{{ $photoUrl }}" alt="Applicant photo" class="h-32 w-32 object-cover rounded border border-gray-200 mx-auto">
                            @else
                                <p class="text-sm text-gray-400">N/A</p>
                            @endif
                        </div>
                        <div class="rounded-md border border-gray-200 p-2 bg-gray-50 text-center">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Signature</p>
                            @if ($signatureUrl)
                                <img src="{{ $signatureUrl }}" alt="Applicant signature" class="h-20 w-40 object-contain rounded border border-gray-200 mx-auto bg-white">
                            @else
                                <p class="text-sm text-gray-400">N/A</p>
                            @endif
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-white shadow-sm border border-gray-200 rounded-lg p-6">
                <h4 class="text-base font-semibold text-gray-900">Personal & Address</h4>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mt-4 text-sm">
                    <div class="space-y-2">
                        <p><span class="font-semibold text-gray-700">Father's Name:</span> {{ $toText(data_get($personal, 'father_name')) }}</p>
                        <p><span class="font-semibold text-gray-700">Mother's Name:</span> {{ $toText(data_get($personal, 'mother_name')) }}</p>
                        <p><span class="font-semibold text-gray-700">Present Address:</span> {{ $formatAddress($presentAddress) }}</p>
                        <p><span class="font-semibold text-gray-700">Permanent Address:</span> {{ $formatAddress($permanentAddress) }}</p>
                    </div>
                    <div class="space-y-2">
                        <p><span class="font-semibold text-gray-700">Total Experience (Years):</span> {{ $toText(data_get($job, 'total_years')) }}</p>
                        <p><span class="font-semibold text-gray-700">Current Job:</span> {{ $toText(data_get($job, 'current.designation')) }} @ {{ $toText(data_get($job, 'current.organization_name')) }}</p>
                        <p><span class="font-semibold text-gray-700">Current Category:</span> {{ $toText(data_get($job, 'current.job_category')) }}</p>
                        <p><span class="font-semibold text-gray-700">Previous Job:</span> {{ $toText(data_get($job, 'previous.designation')) }} @ {{ $toText(data_get($job, 'previous.organization_name')) }}</p>
                    </div>
                </div>
            </section>

            <section class="bg-white shadow-sm border border-gray-200 rounded-lg p-6">
                <h4 class="text-base font-semibold text-gray-900">Education</h4>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold text-gray-600 uppercase text-xs">Level</th>
                                <th class="px-3 py-2 text-left font-semibold text-gray-600 uppercase text-xs">Exam / Subject</th>
                                <th class="px-3 py-2 text-left font-semibold text-gray-600 uppercase text-xs">Institute / Board</th>
                                <th class="px-3 py-2 text-left font-semibold text-gray-600 uppercase text-xs">Result</th>
                                <th class="px-3 py-2 text-left font-semibold text-gray-600 uppercase text-xs">Year</th>
                                <th class="px-3 py-2 text-left font-semibold text-gray-600 uppercase text-xs">Documents</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($educationLabels as $key => $label)
                                @php
                                    $row = data_get($education, $key, []);
                                    $marksheetPath = data_get($educationDocuments, $key.'.marksheet');
                                    $certificatePath = data_get($educationDocuments, $key.'.certificate');
                                    $marksheetUrl = $publicUrl($marksheetPath);
                                    $certificateUrl = $publicUrl($certificatePath);
                                    $examTitle = data_get($row, 'examination');
                                    if (in_array($key, ['graduation', 'masters'], true) && filled(data_get($row, 'subject'))) {
                                        $examTitle = trim(($examTitle ?: 'N/A').' - '.data_get($row, 'subject'));
                                    }
                                    $instituteOrBoard = data_get($row, 'institution') ?: data_get($row, 'education_board');
                                @endphp
                                <tr>
                                    <td class="px-3 py-3 font-medium text-gray-900">{{ $label }}</td>
                                    <td class="px-3 py-3 text-gray-700">{{ $toText($examTitle) }}</td>
                                    <td class="px-3 py-3 text-gray-700">{{ $toText($instituteOrBoard) }}</td>
                                    <td class="px-3 py-3 text-gray-700">{{ $toText(data_get($row, 'result')) }} <span class="text-gray-500">({{ $toText(data_get($row, 'result_scale')) }})</span></td>
                                    <td class="px-3 py-3 text-gray-700">{{ $toText(data_get($row, 'passing_year')) }}</td>
                                    <td class="px-3 py-3">
                                        <div class="flex flex-wrap items-center gap-2">
                                            @if ($marksheetUrl)
                                                <a href="{{ $marksheetUrl }}" download class="inline-flex items-center rounded-md border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">Marksheet</a>
                                            @endif
                                            @if ($certificateUrl)
                                                <a href="{{ $certificateUrl }}" download class="inline-flex items-center rounded-md border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">Certificate</a>
                                            @endif
                                            @if (! $marksheetUrl && ! $certificateUrl)
                                                <span class="text-xs text-gray-400">N/A</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="bg-white shadow-sm border border-gray-200 rounded-lg p-6">
                <h4 class="text-base font-semibold text-gray-900">Program Preferences</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-4 text-sm">
                    @foreach ($programChoices as $label => $value)
                        <p><span class="font-semibold text-gray-700">{{ $label }}:</span> {{ $toText($value) }}</p>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</x-app-layout>

