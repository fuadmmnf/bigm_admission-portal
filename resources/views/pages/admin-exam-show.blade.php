<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $exam->name }}</h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    Paid Applicants: <strong class="text-emerald-700">{{ $totalPaid }}</strong>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.exams.edit', $exam) }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-black">Edit</a>
                <a href="{{ route('admin.exams.'.($exam->status === 'closed' ? 'complete' : $exam->status)) }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest">Back</a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Exam summary --}}
            <div class="bg-white shadow-sm border border-gray-200 rounded-lg px-3 py-2">
                <div class="overflow-x-auto">
                    <dl class="min-w-max flex items-center gap-2 text-xs whitespace-nowrap">
                        <div
                            class="inline-flex items-center gap-1.5 rounded-md border border-gray-200 bg-gray-50 px-2 py-1">
                            <dt class="text-gray-500">Status:</dt>
                            <dd class="font-semibold text-gray-900">{{ $exam->status === 'closed' ? 'complete' : $exam->status }}</dd>
                        </div>
                        <div
                            class="inline-flex items-center gap-1.5 rounded-md border border-gray-200 bg-gray-50 px-2 py-1">
                            <dt class="text-gray-500">Application Window:</dt>
                            <dd class="font-semibold text-gray-900">{{ optional($exam->start_date)->format('d M Y') ?? 'N/A' }}
                                – {{ optional($exam->end_date)->format('d M Y') ?? 'N/A' }}</dd>
                        </div>
                        <div
                            class="inline-flex items-center gap-1.5 rounded-md border border-emerald-200 bg-emerald-50 px-2 py-1">
                            <dt class="text-emerald-700">Paid:</dt>
                            <dd class="font-semibold text-emerald-700">{{ $totalPaid }}</dd>
                        </div>
                        <div
                            class="inline-flex items-center gap-1.5 rounded-md border border-amber-200 bg-amber-50 px-2 py-1">
                            <dt class="text-amber-700">Viva:</dt>
                            <dd class="font-semibold text-amber-700">{{ $totalViva }}</dd>
                        </div>
                        <div
                            class="inline-flex items-center gap-1.5 rounded-md border border-purple-200 bg-purple-50 px-2 py-1">
                            <dt class="text-purple-700">Program:</dt>
                            <dd class="font-semibold text-purple-700">{{ $totalProgram }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Paid applicant list + send-admit-card form --}}
            <form
                id="admit-card-form"
                method="POST"
                action="{{ route('admin.exams.send-admit-cards', $exam) }}"
                x-data="admitCardForm()"
            >
                @csrf
                <input type="hidden" name="send_scope" :value="sendScope">
                <input type="hidden" name="target_stage" :value="targetStage">
                <input type="hidden" name="active_tab" value="{{ $activeTab }}">

                <section class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">

                    {{-- Toolbar --}}
                    <div class="px-4 py-3 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <h3 class="font-semibold text-gray-900">Applicant List</h3>
                            <span
                                class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                {{ $applications->total() }} on this tab
                            </span>
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            <div class="flex items-center gap-2">
                                {{-- Save All Changes — appears when any row input is dirty --}}
                                 <button
                                     type="submit"
                                     x-show="$store.marksChanges.count > 0"
                                     x-cloak
                                     @if($activeTab === 'alumni') style="display:none" @endif
                                     formaction="{{ route('admin.exams.applications.assessment.bulk', $exam) }}"
                                    class="inline-flex items-center gap-1.5 rounded-md border border-yellow-500 bg-yellow-300 px-3 py-1.5 text-xs font-extrabold text-gray-900 shadow-md ring-1 ring-yellow-400 hover:bg-yellow-400"
                                >
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor"
                                         stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M17 3H7a2 2 0 00-2 2v16l7-3 7 3V5a2 2 0 00-2-2z"/>
                                    </svg>
                                    Save All Changes
                                    <span
                                        class="inline-flex items-center justify-center rounded-full bg-white/25 px-1.5 py-0.5 text-[10px] font-bold leading-none"
                                        x-text="$store.marksChanges.count"></span>
                                </button>
                                <button
                                    type="submit"
                                    class="inline-flex items-center gap-1.5 rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700"
                                     formaction="{{ route('admin.exams.send-admit-cards', $exam) }}"
                                     x-on:click="sendScope = 'all_paid'; targetStage = ''"
                                     onclick="return confirm('Send email notification to ALL applicants visible on this tab?')"
                                >
                                    @if ($activeTab === 'paid')
                                        Send Admit Card to All
                                    @elseif ($activeTab === 'viva')
                                        Send Viva Notice to All
                                    @else
                                        Send Program Notice to All
                                    @endif
                                </button>
                            </div>
                            <div class="flex items-center gap-2" x-show="selected.length > 0" x-cloak>
                                <span class="text-sm text-gray-600" x-text="`${selected.length} selected`"></span>
                                <button
                                    type="submit"
                                    class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700"
                                     formaction="{{ route('admin.exams.send-admit-cards', $exam) }}"
                                     x-on:click="sendScope = 'selected'; targetStage = ''"
                                     onclick="return confirm('Send email notification to the selected applicants on this tab?')"
                                >
                                    {{--                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"--}}
                                    {{--                                         viewBox="0 0 24 24">--}}
                                    {{--                                        <path stroke-linecap="round" stroke-linejoin="round"--}}
                                    {{--                                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>--}}
                                    {{--                                    </svg>--}}
                                    @if ($activeTab === 'paid')
                                        Send Admit Card(s)
                                    @elseif ($activeTab === 'viva')
                                        Send Viva Notice(s)
                                    @else
                                        Send Program Notice(s)
                                    @endif
                                </button>

                                @if ($activeTab === 'paid')
                                    <button
                                        type="submit"
                                        class="inline-flex items-center gap-1.5 rounded-md bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-700"
                                        formaction="{{ route('admin.exams.applications.stage-update', $exam) }}"
                                        x-on:click="targetStage = 'viva_selected'"
                                        onclick="return confirm('Mark selected applicants as Viva eligible?')"
                                    >
                                        Mark Viva Eligible
                                    </button>
                                @endif

                                @if ($activeTab === 'viva')
                                    <button
                                        type="submit"
                                        class="inline-flex items-center gap-1.5 rounded-md bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700"
                                        formaction="{{ route('admin.exams.applications.stage-update', $exam) }}"
                                        x-on:click="targetStage = 'program_selected'"
                                        onclick="return confirm('Mark selected applicants as Program selected?')"
                                    >
                                        Mark Program Selected
                                    </button>
                                @endif
                            </div>
                            <div x-show="selected.length === 0" class="text-xs text-gray-400 italic">Select applicants
                                to apply bulk actions
                            </div>
                        </div>
                    </div>

                    <div
                        class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex flex-wrap items-center justify-between gap-3">
                        <div class="inline-flex rounded-md border border-gray-200 bg-white p-1 text-xs font-semibold">
                            <a href="{{ route('admin.exams.show', ['exam' => $exam, 'tab' => 'paid', 'sort' => 'appid_asc', 'search' => $activeSearch]) }}"
                               class="rounded px-3 py-1.5 {{ $activeTab === 'paid' ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">Paid
                                ({{ $totalPaid }})</a>
                            <a href="{{ route('admin.exams.show', ['exam' => $exam, 'tab' => 'viva', 'sort' => 'appid_asc', 'search' => $activeSearch]) }}"
                               class="rounded px-3 py-1.5 {{ $activeTab === 'viva' ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">Viva
                                Selected ({{ $totalViva }})</a>
                            <a href="{{ route('admin.exams.show', ['exam' => $exam, 'tab' => 'program', 'sort' => 'appid_asc', 'search' => $activeSearch]) }}"
                               class="rounded px-3 py-1.5 {{ $activeTab === 'program' ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">Program
                                Selected ({{ $totalProgram }})</a>
                        </div>
                        <form method="GET" action="{{ route('admin.exams.show', $exam) }}"
                              class="ml-auto flex items-center gap-2 flex-wrap justify-end">
                            <input type="hidden" name="tab" value="{{ $activeTab }}">
                            <label for="sort" class="text-xs font-medium text-gray-600">Sort</label>
                            <select id="sort" name="sort" class="w-44 rounded-md border-gray-300 text-xs"
                                    onchange="this.form.submit()">
                                {{-- Common: App ID --}}
                                <option value="appid_asc" @selected($activeSort === 'appid_asc')>App ID (A → Z)</option>

                                {{-- Written marks — all tabs --}}
                                <option value="written_desc" @selected($activeSort === 'written_desc')>Written (High → Low)</option>
                                <option value="written_asc"  @selected($activeSort === 'written_asc')>Written (Low → High)</option>

                                @if ($activeTab === 'viva' || $activeTab === 'program')
                                    {{-- Viva marks — viva + program tabs --}}
                                    <option value="viva_desc" @selected($activeSort === 'viva_desc')>Viva (High → Low)</option>
                                    <option value="viva_asc"  @selected($activeSort === 'viva_asc')>Viva (Low → High)</option>
                                    {{-- Total — viva + program tabs --}}
                                    <option value="total_desc" @selected($activeSort === 'total_desc')>Total (High → Low)</option>
                                    <option value="total_asc"  @selected($activeSort === 'total_asc')>Total (Low → High)</option>
                                @endif

                                @if ($activeTab === 'program')
                                    {{-- Group by program — program tab only --}}
                                    <option value="program_asc" @selected($activeSort === 'program_asc')>Program (A → Z) + Total ↓</option>
                                @endif
                            </select>
                            <input
                                type="text"
                                name="search"
                                value="{{ $activeSearch }}"
                                placeholder="Name, phone, email, App ID"
                                class="w-56 rounded-md border-gray-300 text-xs"
                            >
                            <button type="submit"
                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100">
                                Apply
                            </button>
                            @if ($activeSearch !== '' || $activeSort !== 'appid_asc')
                                <a href="{{ route('admin.exams.show', ['exam' => $exam, 'tab' => $activeTab]) }}"
                                   class="inline-flex items-center rounded-md px-2 py-1.5 text-xs font-medium text-gray-500 hover:text-gray-700"
                                   title="Clear filters">Clear</a>
                            @endif
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 w-10">
                                    <input
                                        type="checkbox"
                                        class="rounded border-gray-300 text-indigo-600 cursor-pointer"
                                        :checked="allSelected"
                                        x-on:change="toggleAll($event)"
                                        title="Select / deselect all on this page"
                                    >
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-10">#</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">App ID</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Name</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Phone</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Gender</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Selection Stage</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Assessment</th>
                                @if($activeTab === 'viva' || $activeTab === 'program')
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Total</th>
                                @endif
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                            @if ($applications->isNotEmpty())
                                @foreach ($applications as $index => $application)
                                    <tr
                                        class="transition-colors"
                                        :class="selected.includes('{{ $application->ulid }}') ? 'bg-indigo-50' : 'hover:bg-gray-50'"
                                    >
                                        <td class="px-4 py-3">
                                            <input
                                                type="checkbox"
                                                name="application_ids[]"
                                                value="{{ $application->ulid }}"
                                                class="rounded border-gray-300 text-indigo-600 cursor-pointer"
                                                x-model="selected"
                                            >
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $applications->firstItem() + $index }}</td>
                                        <td class="px-4 py-3 text-sm font-mono font-semibold text-indigo-700 whitespace-nowrap">
                                            {{ $application->application_id ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $application->applicant_name }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $application->applicant_email }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $application->applicant_phone }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $application->gender ?? data_get($application->additional_info, 'personal.gender', 'N/A') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ str($application->selection_stage ?? 'paid')->replace('_', ' ')->title() }}</td>
                                        <td class="px-4 py-3 text-xs text-gray-600">
                                            <div class="space-y-1 min-w-[14rem]">
                                                @if ($activeTab === 'paid')
                                                    {{-- Per-row written marks tracker --}}
                                                    <div x-data="trackableMarks(
                                                            @js(old('written_marks.'.$application->ulid, $application->written_exam_marks)),
                                                            @js($application->ulid)
                                                        )">
                                                        <div class="flex items-center gap-1.5 mb-1">
                                                            <label class="text-[11px] font-semibold text-gray-700">Written</label>
                                                        </div>
                                                        <div class="flex items-center gap-1.5">
                                                            <input
                                                                type="number"
                                                                step="0.01"
                                                                min="0"
                                                                name="written_marks[{{ $application->ulid }}]"
                                                                x-model="current"
                                                                @input="onInput"
                                                                class="no-spinner w-16 rounded-md border-gray-300 text-xs"
                                                            >
                                                        </div>
                                                    </div>
                                                @elseif ($activeTab === 'viva')
                                                    <p><span
                                                            class="font-semibold text-gray-700">Written:</span> {{ $application->written_exam_marks !== null ? number_format((float) $application->written_exam_marks, 2) : 'N/A' }}
                                                    </p>
                                                    {{-- Per-row viva marks tracker --}}
                                                    <div x-data="trackableMarks(
                                                            @js(old('viva_marks.'.$application->ulid, $application->viva_exam_marks)),
                                                            @js($application->ulid)
                                                        )">
                                                        <div class="flex items-center gap-1.5 mb-1">
                                                            <label
                                                                class="text-[11px] font-semibold text-gray-700">Viva</label>
                                                        </div>
                                                        <div class="flex items-center gap-1.5">
                                                            <input
                                                                type="number"
                                                                step="0.01"
                                                                min="0"
                                                                name="viva_marks[{{ $application->ulid }}]"
                                                                x-model="current"
                                                                @input="onInput"
                                                                class="no-spinner w-16 rounded-md border-gray-300 text-xs"
                                                            >
                                                        </div>
                                                    </div>
                                                @else
                                                    <p><span
                                                            class="font-semibold text-gray-700">Written:</span> {{ $application->written_exam_marks !== null ? number_format((float) $application->written_exam_marks, 2) : 'N/A' }}
                                                    </p>
                                                    <p><span
                                                            class="font-semibold text-gray-700">Viva:</span> {{ $application->viva_exam_marks !== null ? number_format((float) $application->viva_exam_marks, 2) : 'N/A' }}
                                                    </p>
                                                    {{-- Per-row program selection tracker --}}
                                                    <div x-data="trackableProgram(
                                                            @js(old('selected_category_ids.'.$application->ulid, $application->selected_category_id)),
                                                            @js($application->ulid)
                                                        )">
                                                        <div class="flex items-center gap-1.5 mb-1">
                                                            <label class="text-[11px] font-semibold text-gray-700">Program</label>
                                                        </div>
                                                        <div class="flex items-center gap-1.5">
                                                            <select
                                                                name="selected_category_ids[{{ $application->ulid }}]"
                                                                x-model="current"
                                                                @change="onInput"
                                                                class="w-full rounded-md border-gray-300 text-xs"
                                                            >
                                                                <option value="">Not selected</option>
                                                                @foreach ($programCategories as $category)
                                                                    <option
                                                                        value="{{ $category->id }}">{{ $category->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                 @endif
                                            </div>
                                        </td>
                                        @if($activeTab === 'viva' || $activeTab === 'program')
                                        @php
                                            $totalMarks = (float)($application->written_exam_marks ?? 0)
                                                        + (float)($application->viva_exam_marks ?? 0);
                                        @endphp
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 whitespace-nowrap">
                                            {{ $application->written_exam_marks !== null || $application->viva_exam_marks !== null
                                                ? number_format($totalMarks, 2)
                                                : '—' }}
                                        </td>
                                        @endif
                                        <td class="px-4 py-3 text-sm whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <a
                                                    href="{{ route('admin.applications.show', ['application' => $application, 'tab' => $activeTab]) }}"
                                                    class="inline-flex items-center rounded-md border border-gray-200 bg-white px-2.5 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                                                >
                                                    Details
                                                </a>
                                                <a
                                                    href="{{ route('admin.applications.admit-card', $application) }}"
                                                    target="_blank"
                                                    rel="noopener"
                                                    class="inline-flex items-center rounded-md border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100"
                                                >
                                                    View Card
                                                </a>
                                                @if (optional(auth()->user())->hasRole('admin'))
                                                    <button
                                                        type="button"
                                                        class="inline-flex items-center justify-center text-red-600 hover:text-red-700"
                                                        title="Delete application"
                                                        x-on:click="removeApplication('{{ route('admin.applications.destroy', $application) }}')"
                                                    >
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                             stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M6 7h12M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m-8 0l1 12a1 1 0 001 1h6a1 1 0 001-1l1-12"/>
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="{{ $activeTab === 'viva' || $activeTab === 'program' ? 11 : 10 }}" class="px-4 py-8 text-center text-sm text-gray-500">
                                        No applicants found for this tab.
                                    </td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>

                    {{-- Footer: page info + bulk-select helpers --}}
                    @if ($applications->total() > 0)
                        <div
                            class="flex items-center justify-between px-4 py-3 border-t border-gray-200 bg-gray-50 gap-3 flex-wrap">
                            <div class="text-xs text-gray-500">
                                Showing {{ $applications->firstItem() }}–{{ $applications->lastItem() }}
                                of {{ $applications->total() }} applicants
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" x-on:click="selectAll()"
                                        class="text-xs font-medium text-indigo-600 hover:underline">Select all on page
                                </button>
                                <span class="text-gray-300">|</span>
                                <button type="button" x-on:click="clearAll()" x-show="selected.length > 0"
                                        class="text-xs font-medium text-gray-500 hover:underline" x-cloak>Clear
                                    selection
                                </button>
                            </div>
                        </div>
                    @endif

                    <div class="px-4 py-4 border-t border-gray-100">
                        {{ $applications->links() }}
                    </div>
                </section>
            </form>
        </div>
    </div>

    <form id="delete-application-form" method="POST" style="display:none">
        @csrf
        @method('DELETE')
    </form>

    <style>
        input.no-spinner::-webkit-outer-spin-button,
        input.no-spinner::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input.no-spinner[type='number'] {
            -moz-appearance: textfield;
            appearance: textfield;
        }
    </style>

    <script>

        // Global store: tracks which row ULIDs have unsaved changes.
        // The toolbar "Save All Changes" badge reads count from here.
        document.addEventListener('alpine:init', () => {
            Alpine.store('marksChanges', {
                dirty: new Set(),
                markDirty(id) {
                    this.dirty.add(id);
                },
                markClean(id) {
                    this.dirty.delete(id);
                },
                get count() {
                    return this.dirty.size;
                },
            });
        });

        function admitCardForm() {
            const pageUlids = @js($applications->pluck('ulid')->toArray());

            return {
                selected: [],
                sendScope: 'selected',
                targetStage: '',  // only set by stage-update buttons — never by admit-card buttons

                get allSelected() {
                    return pageUlids.length > 0 && pageUlids.every(u => this.selected.includes(u));
                },

                toggleAll(event) {
                    event.target.checked ? this.selectAll() : this.clearAll();
                },

                selectAll() {
                    pageUlids.forEach(u => {
                        if (!this.selected.includes(u)) this.selected.push(u);
                    });
                },

                clearAll() {
                    this.selected = this.selected.filter(u => !pageUlids.includes(u));
                },

                removeApplication(deleteUrl) {
                    if (!confirm('Confirm delete of this application? This action can be restored only from soft-deleted records.')) return;

                    const form = document.getElementById('delete-application-form');
                    if (!form) return;

                    form.action = deleteUrl;
                    form.submit();
                },
            };
        }

        /**
         * Per-row marks tracker (written / viva).
         * Tracks dirty state only; bulk save is done from the navbar button.
         *
         * @param {number|null} initialValue  – Raw value from DB (may be null)
         * @param {string}      ulid          – Application ULID
         */
        function trackableMarks(initialValue, ulid) {
            const normalize = (v) => (v !== null && v !== undefined && v !== '') ? String(parseFloat(v)) : '';
            const normalizedInitial = normalize(initialValue);

            return {
                initial: normalizedInitial,
                current: normalizedInitial,
                ulid,
                dirty: false,

                onInput() {
                    const normalized = normalize(this.current);
                    this.dirty = normalized !== this.initial;
                    if (this.dirty) {
                        Alpine.store('marksChanges').markDirty(this.ulid);
                    } else {
                        Alpine.store('marksChanges').markClean(this.ulid);
                    }
                },
            };
        }

        /**
         * Per-row program selection tracker.
         * Tracks dirty state only; bulk save is done from the navbar button.
         *
         * @param {number|string|null} initialValue  – selected_category_id from DB
         * @param {string}             ulid          – Application ULID
         */
        function trackableProgram(initialValue, ulid) {
            const normalize = (v) => (v !== null && v !== undefined) ? String(v) : '';
            const normalizedInitial = normalize(initialValue);

            return {
                initial: normalizedInitial,
                current: normalizedInitial,
                ulid,
                dirty: false,

                onInput() {
                    this.dirty = this.current !== this.initial;
                    if (this.dirty) {
                        Alpine.store('marksChanges').markDirty(this.ulid);
                    } else {
                        Alpine.store('marksChanges').markClean(this.ulid);
                    }
                },
            };
        }
    </script>
</x-app-layout>

