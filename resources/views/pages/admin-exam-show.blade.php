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
                <a href="{{ route('admin.exams.edit', $exam) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-black">Edit</a>
                <a href="{{ route('admin.exams.'.($exam->status === 'closed' ? 'complete' : $exam->status)) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest">Back</a>
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
            <div class="bg-white shadow-sm border border-gray-200 rounded-lg p-6">
                <div class="overflow-x-auto">
                <dl class="min-w-[980px] grid grid-cols-5 gap-4 text-sm">
                    <div class="rounded-md border border-gray-100 bg-gray-50 p-3">
                        <dt class="text-gray-500">Status</dt>
                        <dd class="font-semibold text-gray-900">{{ $exam->status === 'closed' ? 'complete' : $exam->status }}</dd>
                    </div>
                    <div class="rounded-md border border-gray-100 bg-gray-50 p-3">
                        <dt class="text-gray-500">Application Window</dt>
                        <dd class="font-semibold text-gray-900">
                            {{ optional($exam->start_date)->format('d M Y') ?? 'N/A' }} – {{ optional($exam->end_date)->format('d M Y') ?? 'N/A' }}
                        </dd>
                    </div>
                    <div class="rounded-md border border-gray-100 bg-gray-50 p-3">
                        <dt class="text-gray-500">Paid Applicants</dt>
                        <dd class="font-semibold text-emerald-700">{{ $totalPaid }}</dd>
                    </div>
                    <div class="rounded-md border border-gray-100 bg-gray-50 p-3">
                        <dt class="text-gray-500">Viva Selected</dt>
                        <dd class="font-semibold text-amber-700">{{ $totalViva }}</dd>
                    </div>
                    <div class="rounded-md border border-gray-100 bg-gray-50 p-3">
                        <dt class="text-gray-500">Program Selected</dt>
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

                <section class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">

                    {{-- Toolbar --}}
                    <div class="px-4 py-3 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <h3 class="font-semibold text-gray-900">Applicant List</h3>
                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                {{ $applications->total() }} on this tab
                            </span>
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            <div class="flex items-center gap-2">
                                <button
                                    type="submit"
                                    class="inline-flex items-center gap-1.5 rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700"
                                    formaction="{{ route('admin.exams.send-admit-cards', $exam) }}"
                                    x-on:click="sendScope = 'all_paid'"
                                    onclick="return confirm('Send admit card emails to ALL paid applicants for this exam?')"
                                >
                                    Send All Paid
                                </button>
                            </div>
                            <div class="flex items-center gap-2" x-show="selected.length > 0" x-cloak>
                                <span class="text-sm text-gray-600" x-text="`${selected.length} selected`"></span>
                                <button
                                    type="submit"
                                    class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700"
                                    formaction="{{ route('admin.exams.send-admit-cards', $exam) }}"
                                    x-on:click="sendScope = 'selected'"
                                    onclick="return confirm('Send admit card emails to the selected applicants?')"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    Send Admit Card(s)
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
                                        class="inline-flex items-center gap-1.5 rounded-md bg-purple-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-purple-700"
                                        formaction="{{ route('admin.exams.applications.stage-update', $exam) }}"
                                        x-on:click="targetStage = 'program_selected'"
                                        onclick="return confirm('Mark selected applicants as Program selected?')"
                                    >
                                        Mark Program Selected
                                    </button>
                                @endif
                            </div>
                            <div x-show="selected.length === 0" class="text-xs text-gray-400 italic">Select applicants to apply bulk actions</div>
                        </div>
                    </div>

                    <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                        <div class="inline-flex rounded-md border border-gray-200 bg-white p-1 text-xs font-semibold">
                            <a href="{{ route('admin.exams.show', ['exam' => $exam, 'tab' => 'paid']) }}" class="rounded px-3 py-1.5 {{ $activeTab === 'paid' ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">Paid ({{ $totalPaid }})</a>
                            <a href="{{ route('admin.exams.show', ['exam' => $exam, 'tab' => 'viva']) }}" class="rounded px-3 py-1.5 {{ $activeTab === 'viva' ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">Viva Selected ({{ $totalViva }})</a>
                            <a href="{{ route('admin.exams.show', ['exam' => $exam, 'tab' => 'program']) }}" class="rounded px-3 py-1.5 {{ $activeTab === 'program' ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">Program Selected ({{ $totalProgram }})</a>
                        </div>
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
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Email</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Phone</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Selection Stage</th>
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
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $application->applicant_name }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-700">{{ $application->applicant_email }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-700">{{ $application->applicant_phone }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                                    Paid
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">{{ str($application->selection_stage ?? 'paid')->replace('_', ' ')->title() }}</td>
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
                                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m-8 0l1 12a1 1 0 001 1h6a1 1 0 001-1l1-12" />
                                                            </svg>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">
                                            No applicants found for this tab.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    {{-- Footer: page info + bulk-select helpers --}}
                    @if ($applications->total() > 0)
                        <div class="flex items-center justify-between px-4 py-3 border-t border-gray-200 bg-gray-50 gap-3 flex-wrap">
                            <div class="text-xs text-gray-500">
                                Showing {{ $applications->firstItem() }}–{{ $applications->lastItem() }} of {{ $applications->total() }} applicants
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" x-on:click="selectAll()" class="text-xs font-medium text-indigo-600 hover:underline">Select all on page</button>
                                <span class="text-gray-300">|</span>
                                <button type="button" x-on:click="clearAll()" x-show="selected.length > 0" class="text-xs font-medium text-gray-500 hover:underline" x-cloak>Clear selection</button>
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

    <script>
        function admitCardForm() {
            const pageUlids = @js($applications->pluck('ulid')->toArray());

            return {
                selected: [],
                sendScope: 'selected',
                targetStage: 'viva_selected',

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
    </script>
</x-app-layout>

