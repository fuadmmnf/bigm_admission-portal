<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $exam->name }}</h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    Paid: <strong class="text-emerald-700">{{ $totalPaid }}</strong>
                    <span class="text-gray-400 mx-1">/</span>
                    Total submitted: <strong class="text-gray-600">{{ $totalAll }}</strong>
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
                <dl class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Status</dt>
                        <dd class="font-semibold text-gray-900">{{ $exam->status === 'closed' ? 'complete' : $exam->status }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Application Window</dt>
                        <dd class="font-semibold text-gray-900">
                            {{ optional($exam->start_date)->format('d M Y') ?? 'N/A' }} – {{ optional($exam->end_date)->format('d M Y') ?? 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Paid Applicants</dt>
                        <dd class="font-semibold text-emerald-700">{{ $totalPaid }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Total Submitted</dt>
                        <dd class="font-semibold text-gray-900">{{ $totalAll }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Paid applicant list + send-admit-card form --}}
            <form
                id="admit-card-form"
                method="POST"
                action="{{ route('admin.exams.send-admit-cards', $exam) }}"
                x-data="admitCardForm()"
            >
                @csrf

                <section class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">

                    {{-- Toolbar --}}
                    <div class="px-4 py-3 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <h3 class="font-semibold text-gray-900">Paid Applicants</h3>
                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                {{ $applications->total() }} paid
                            </span>
                        </div>

                        {{-- Send button – visible only when ≥1 row selected AND exam is active --}}
                        @if ($exam->status === 'active')
                            <div class="flex items-center gap-2" x-show="selected.length > 0" x-cloak>
                                <span class="text-sm text-gray-600" x-text="`${selected.length} selected`"></span>
                                <button
                                    type="submit"
                                    class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700"
                                    onclick="return confirm('Send admit card emails to the selected applicants?')"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    Send Admit Card(s)
                                </button>
                            </div>
                            <div x-show="selected.length === 0" class="text-xs text-gray-400 italic">Select applicants to send admit cards</div>
                        @else
                            <span class="text-xs text-amber-600 font-medium">Admit card emails are available for active exams only</span>
                        @endif
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    @if ($exam->status === 'active')
                                        <th class="px-4 py-3 w-10">
                                            <input
                                                type="checkbox"
                                                class="rounded border-gray-300 text-indigo-600 cursor-pointer"
                                                :checked="allSelected"
                                                @change="toggleAll($event)"
                                                title="Select / deselect all on this page"
                                            >
                                        </th>
                                    @endif
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-10">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Email</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Phone</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($applications as $index => $application)
                                    <tr
                                        class="transition-colors"
                                        :class="selected.includes('{{ $application->ulid }}') ? 'bg-indigo-50' : 'hover:bg-gray-50'"
                                    >
                                        @if ($exam->status === 'active')
                                            <td class="px-4 py-3">
                                                <input
                                                    type="checkbox"
                                                    name="application_ids[]"
                                                    value="{{ $application->ulid }}"
                                                    class="rounded border-gray-300 text-indigo-600 cursor-pointer"
                                                    x-model="selected"
                                                >
                                            </td>
                                        @endif
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $applications->firstItem() + $index }}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $application->applicant_name }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $application->applicant_email }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $application->applicant_phone }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                                Paid
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <a
                                                    href="{{ route('admin.applications.admit-card', $application) }}"
                                                    target="_blank"
                                                    rel="noopener"
                                                    class="inline-flex items-center rounded-md border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100"
                                                >
                                                    View Card
                                                </a>
                                                @if ($exam->status === 'active')
                                                    <button
                                                        type="button"
                                                        class="inline-flex items-center rounded-md border border-gray-200 bg-white px-2.5 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                                                        @click="quickSend('{{ $application->ulid }}')"
                                                        title="Send admit card email to this applicant"
                                                    >
                                                        Send Email
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $exam->status === 'active' ? 7 : 6 }}" class="px-4 py-8 text-center text-sm text-gray-500">
                                            No paid applicants yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Footer: page info + bulk-select helpers --}}
                    @if ($applications->total() > 0)
                        <div class="flex items-center justify-between px-4 py-3 border-t border-gray-200 bg-gray-50 gap-3 flex-wrap">
                            <div class="text-xs text-gray-500">
                                Showing {{ $applications->firstItem() }}–{{ $applications->lastItem() }} of {{ $applications->total() }} paid applicants
                            </div>
                            @if ($exam->status === 'active')
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="selectAll()" class="text-xs font-medium text-indigo-600 hover:underline">Select all on page</button>
                                    <span class="text-gray-300">|</span>
                                    <button type="button" @click="clearAll()" x-show="selected.length > 0" class="text-xs font-medium text-gray-500 hover:underline" x-cloak>Clear selection</button>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="px-4 py-4 border-t border-gray-100">
                        {{ $applications->links() }}
                    </div>
                </section>
            </form>
        </div>
    </div>

    {{-- Hidden form used by the per-row "Send Email" button to post a single ULID --}}
    @if ($exam->status === 'active')
        <form id="quick-send-form" method="POST" action="{{ route('admin.exams.send-admit-cards', $exam) }}" style="display:none">
            @csrf
            <input type="hidden" name="application_ids[]" id="quick-send-ulid">
        </form>
    @endif

    <script>
        function admitCardForm() {
            const pageUlids = @js($applications->pluck('ulid')->toArray());

            return {
                selected: [],

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

                quickSend(ulid) {
                    if (!confirm('Send the admit card email to this applicant?')) return;
                    const form = document.getElementById('quick-send-form');
                    if (!form) return;
                    document.getElementById('quick-send-ulid').value = ulid;
                    form.submit();
                },
            };
        }
    </script>
</x-app-layout>

