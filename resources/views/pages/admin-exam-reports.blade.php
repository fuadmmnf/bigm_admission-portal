<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Exam Reports</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $exam->name }}</p>
            </div>
            <a
                href="{{ route('admin.exams.show', $exam) }}"
                class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest"
            >
                Back to Applicants
            </a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <section class="bg-white border border-gray-200 rounded-lg px-3 py-2">
                <div class="overflow-x-auto">
                    <dl class="min-w-max flex items-center gap-2 text-xs whitespace-nowrap">
                        <div class="inline-flex items-center gap-1.5 rounded-md border border-gray-200 bg-gray-50 px-2 py-1">
                            <dt class="text-gray-500">Status:</dt>
                            <dd class="font-semibold text-gray-900">{{ ucfirst($exam->status) }}</dd>
                        </div>
                        <div class="inline-flex items-center gap-1.5 rounded-md border border-gray-200 bg-gray-50 px-2 py-1">
                            <dt class="text-gray-500">Window:</dt>
                            <dd class="font-semibold text-gray-900">{{ optional($exam->start_date)->format('d M Y') ?? 'N/A' }} – {{ optional($exam->end_date)->format('d M Y') ?? 'N/A' }}</dd>
                        </div>
                        <div class="inline-flex items-center gap-1.5 rounded-md border border-emerald-200 bg-emerald-50 px-2 py-1">
                            <dt class="text-emerald-700">Paid Applicants:</dt>
                            <dd class="font-semibold text-emerald-700">{{ $paidApplicantsCount }}</dd>
                        </div>
                        <div class="inline-flex items-center gap-1.5 rounded-md border border-amber-200 bg-amber-50 px-2 py-1">
                            <dt class="text-amber-700">Shortlisted for Viva:</dt>
                            <dd class="font-semibold text-amber-700">{{ $vivaSelectedCount }}</dd>
                        </div>
                        <div class="inline-flex items-center gap-1.5 rounded-md border border-purple-200 bg-purple-50 px-2 py-1">
                            <dt class="text-purple-700">Program Enrolled:</dt>
                            <dd class="font-semibold text-purple-700">{{ $programSelectedCount }}</dd>
                        </div>
                    </dl>
                </div>
            </section>

            <section class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
                <h3 class="text-base font-semibold text-gray-900">Available Reports</h3>
                <p class="mt-1 text-sm text-gray-500">More reports can be added here one by one.</p>

                {{-- Attendance List --}}
                <div class="mt-4 rounded-lg border border-gray-200 p-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Attendance List (Paid Applicants)</p>
                        <p class="text-xs text-gray-500">Opens as PDF stream in a new tab.</p>
                    </div>

                    @if (auth()->user()?->hasRole('admin'))
                        <a
                            href="{{ route('admin.exams.reports.attendance-list', $exam) }}"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                        >
                            Download Attendance Sheet
                        </a>
                    @else
                        <span class="inline-flex items-center px-3 py-2 bg-gray-100 border border-gray-200 rounded-md text-xs font-semibold text-gray-500 uppercase tracking-widest">
                            Admin Only
                        </span>
                    @endif
                </div>

                {{-- Viva Sheet --}}
                <div class="mt-3 rounded-lg border border-gray-200 p-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Viva Sheet</p>
                        <p class="text-xs text-gray-500">Includes applicants selected for viva (including program-selected).</p>
                    </div>

                    @if (auth()->user()?->hasRole('admin'))
                        <a
                            href="{{ route('admin.exams.reports.viva-selected-list', $exam) }}"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                        >
                            Download Viva Sheet
                        </a>
                    @else
                        <span class="inline-flex items-center px-3 py-2 bg-gray-100 border border-gray-200 rounded-md text-xs font-semibold text-gray-500 uppercase tracking-widest">
                            Admin Only
                        </span>
                    @endif
                </div>

                {{-- Gender Wise Report --}}
                <div class="mt-3 rounded-lg border border-gray-200 p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Gender Wise Applicant Report</p>
                            <p class="text-xs text-gray-500">Filter paid applicants by gender and stream as PDF.</p>
                        </div>
                        @if (auth()->user()?->hasRole('admin'))
                            <form method="GET" action="{{ route('admin.exams.reports.gender-wise-applicants', $exam) }}" target="_blank" rel="noopener" class="flex items-center gap-2 flex-wrap">
                                <select name="gender" class="rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-1.5">
                                    <option value="">All Genders</option>
                                    @foreach ($genders as $g)
                                        <option value="{{ $g }}">{{ $g }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                    Download Gender Report
                                </button>
                            </form>
                        @else
                            <span class="inline-flex items-center px-3 py-2 bg-gray-100 border border-gray-200 rounded-md text-xs font-semibold text-gray-500 uppercase tracking-widest">Admin Only</span>
                        @endif
                    </div>
                </div>

                {{-- Employer Wise — form on its own row, right-aligned --}}
                <div class="mt-3 rounded-lg border border-gray-200 p-4">
                    <div class="flex flex-col gap-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Employer Wise Report</p>
                            <p class="text-xs text-gray-500">Filter paid applicants by current employer category and stream as PDF.</p>
                        </div>
                        @if (auth()->user()?->hasRole('admin'))
                            <form method="GET" action="{{ route('admin.exams.reports.employer-wise', $exam) }}" target="_blank" rel="noopener" class="flex items-center gap-2 flex-wrap justify-end">
                                <select name="employer" class="rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-1.5">
                                    <option value="">All Employers</option>
                                    @foreach ($jobCategories as $cat)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                    Download Employer Report
                                </button>
                            </form>
                        @else
                            <div class="flex justify-end">
                                <span class="inline-flex items-center px-3 py-2 bg-gray-100 border border-gray-200 rounded-md text-xs font-semibold text-gray-500 uppercase tracking-widest">Admin Only</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Choice List Wise Report &mdash; Style 1 (All Applicants) --}}
                <div class="mt-3 rounded-lg border border-gray-200 p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Choice List (All Applicants)</p>
                            <p class="text-xs text-gray-500">All paid applicants with written &amp; viva marks and all 6 course choices in columns (landscape PDF).</p>
                        </div>
                        @if (auth()->user()?->hasRole('admin'))
                            <a href="{{ route('admin.exams.reports.choice-list-wise', $exam) }}" target="_blank" rel="noopener" class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                Download Choice Report (All)
                            </a>
                        @else
                            <span class="inline-flex items-center px-3 py-2 bg-gray-100 border border-gray-200 rounded-md text-xs font-semibold text-gray-500 uppercase tracking-widest">Admin Only</span>
                        @endif
                    </div>
                </div>

                {{-- Choice List Wise Report &mdash; Style 2 (By Subject) --}}
                <div class="mt-3 rounded-lg border border-gray-200 p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Choice List (Subject Wise)</p>
                            <p class="text-xs text-gray-500">Select a subject to see applicants grouped by which choice position (1st&ndash;6th) they placed that subject.</p>
                        </div>
                        @if (auth()->user()?->hasRole('admin'))
                            <form method="GET" action="{{ route('admin.exams.reports.choice-by-subject', $exam) }}" target="_blank" rel="noopener" class="flex items-center gap-2 flex-wrap">
                                <select name="subject" required class="rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-1.5">
                                    <option value="">Select Subject&hellip;</option>
                                    @foreach ($programs as $prog)
                                        <option value="{{ $prog }}">{{ $prog }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                    Download By Subject
                                </button>
                            </form>
                        @else
                            <span class="inline-flex items-center px-3 py-2 bg-gray-100 border border-gray-200 rounded-md text-xs font-semibold text-gray-500 uppercase tracking-widest">Admin Only</span>
                        @endif
                    </div>
                </div>


                <div class="mt-3 rounded-lg border border-gray-200 p-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Enrolled Students Report</p>
                        <p class="text-xs text-gray-500">Final selected students (program selected).</p>
                    </div>

                    @if (auth()->user()?->hasRole('admin'))
                        <a href="{{ route('admin.exams.reports.enrolled-students', $exam) }}" target="_blank" rel="noopener" class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            Download Enrolled Students
                        </a>
                    @else
                        <span class="inline-flex items-center px-3 py-2 bg-gray-100 border border-gray-200 rounded-md text-xs font-semibold text-gray-500 uppercase tracking-widest">Admin Only</span>
                    @endif
                </div>


                <div class="mt-3 rounded-lg border border-gray-200 p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Program Selected Report (By Program Code)</p>
                            <p class="text-xs text-gray-500">Select a program code to download applicants selected for that program.</p>
                        </div>
                        @if (auth()->user()?->hasRole('admin'))
                            <form method="GET" action="{{ route('admin.exams.reports.program-selected-by-code', $exam) }}" target="_blank" rel="noopener" class="flex items-center gap-2 flex-wrap">
                                <select name="program_id" required class="rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-1.5">
                                    <option value="">Select Program Code&hellip;</option>
                                    @foreach ($programCategories as $programCategory)
                                        <option value="{{ $programCategory->id }}">
                                            {{ data_get($programCategory->additional_info, 'code', $programCategory->name) }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                    Download Program List
                                </button>
                            </form>
                        @else
                            <span class="inline-flex items-center px-3 py-2 bg-gray-100 border border-gray-200 rounded-md text-xs font-semibold text-gray-500 uppercase tracking-widest">Admin Only</span>
                        @endif
                    </div>
                </div>

                <div class="mt-3 rounded-lg border border-gray-200 p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Program wise CV (1st Choice)</p>
                            <p class="text-xs text-gray-500">Select a program code to download CV pages only for applicants who selected that code as 1st choice.</p>
                        </div>
                        @if (auth()->user()?->hasRole('admin'))
                            <form method="GET" action="{{ route('admin.exams.reports.all-applicant-cvs', $exam) }}" target="_blank" rel="noopener" class="flex items-center gap-2 flex-wrap">
                                <select name="program_id" required class="rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-1.5">
                                    <option value="">Select Program Code&hellip;</option>
                                    @foreach ($programCategories as $programCategory)
                                        <option value="{{ $programCategory->id }}">
                                            {{ data_get($programCategory->additional_info, 'code', $programCategory->name) }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                    Download Program CVs
                                </button>
                            </form>
                        @else
                            <span class="inline-flex items-center px-3 py-2 bg-gray-100 border border-gray-200 rounded-md text-xs font-semibold text-gray-500 uppercase tracking-widest">Admin Only</span>
                        @endif
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>

