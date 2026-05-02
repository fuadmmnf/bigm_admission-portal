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
            <section class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
                <h3 class="text-base font-semibold text-gray-900">Exam Details</h3>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="rounded-md border border-gray-200 bg-gray-50 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Status</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ ucfirst($exam->status) }}</p>
                    </div>
                    <div class="rounded-md border border-gray-200 bg-gray-50 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Application Window</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">
                            {{ optional($exam->start_date)->format('d M Y') ?? 'N/A' }} - {{ optional($exam->end_date)->format('d M Y') ?? 'N/A' }}
                        </p>
                    </div>
                    <div class="rounded-md border border-gray-200 bg-gray-50 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Paid Applicants</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ $paidApplicantsCount }}</p>
                    </div>
                    <div class="rounded-md border border-gray-200 bg-gray-50 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Viva / Program</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ $vivaSelectedCount }} / {{ $programSelectedCount }}</p>
                    </div>
                </div>
            </section>

            <section class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
                <h3 class="text-base font-semibold text-gray-900">Available Reports</h3>
                <p class="mt-1 text-sm text-gray-500">More reports can be added here one by one.</p>

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

                <div class="mt-3 rounded-lg border border-gray-200 p-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Viva Selected List</p>
                        <p class="text-xs text-gray-500">Includes applicants selected for viva (including program-selected).</p>
                    </div>

                    @if (auth()->user()?->hasRole('admin'))
                        <a
                            href="{{ route('admin.exams.reports.viva-selected-list', $exam) }}"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                        >
                            Download Viva List
                        </a>
                    @else
                        <span class="inline-flex items-center px-3 py-2 bg-gray-100 border border-gray-200 rounded-md text-xs font-semibold text-gray-500 uppercase tracking-widest">
                            Admin Only
                        </span>
                    @endif
                </div>

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

                <div class="mt-3 rounded-lg border border-gray-200 p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Employer Wise Report</p>
                            <p class="text-xs text-gray-500">Filter paid applicants by current employer category and stream as PDF.</p>
                        </div>
                        @if (auth()->user()?->hasRole('admin'))
                            <form method="GET" action="{{ route('admin.exams.reports.employer-wise', $exam) }}" target="_blank" rel="noopener" class="flex items-center gap-2 flex-wrap">
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
                            <span class="inline-flex items-center px-3 py-2 bg-gray-100 border border-gray-200 rounded-md text-xs font-semibold text-gray-500 uppercase tracking-widest">Admin Only</span>
                        @endif
                    </div>
                </div>

                <div class="mt-3 rounded-lg border border-gray-200 p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Choice List Wise Report &mdash; Style 1 (All Applicants)</p>
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

                <div class="mt-3 rounded-lg border border-gray-200 p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Choice List Wise Report &mdash; Style 2 (By Subject)</p>
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
                        <p class="text-sm font-semibold text-gray-900">Total Job-Experience Wise Report</p>
                        <p class="text-xs text-gray-500">Placeholder format; content structure will be updated later.</p>
                    </div>

                    @if (auth()->user()?->hasRole('admin'))
                        <a href="{{ route('admin.exams.reports.job-experience-wise', $exam) }}" target="_blank" rel="noopener" class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            Download Job-Experience Report
                        </a>
                    @else
                        <span class="inline-flex items-center px-3 py-2 bg-gray-100 border border-gray-200 rounded-md text-xs font-semibold text-gray-500 uppercase tracking-widest">Admin Only</span>
                    @endif
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

                <div class="mt-3 rounded-lg border border-gray-200 p-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">All Applicant CVs (Single PDF)</p>
                        <p class="text-xs text-gray-500">One combined PDF with each applicant's textual profile, photo, and signature.</p>
                    </div>

                    @if (auth()->user()?->hasRole('admin'))
                        <a href="{{ route('admin.exams.reports.all-applicant-cvs', $exam) }}" target="_blank" rel="noopener" class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            Download All CVs
                        </a>
                    @else
                        <span class="inline-flex items-center px-3 py-2 bg-gray-100 border border-gray-200 rounded-md text-xs font-semibold text-gray-500 uppercase tracking-widest">Admin Only</span>
                    @endif
                </div>
            </section>
        </div>
    </div>
</x-app-layout>

