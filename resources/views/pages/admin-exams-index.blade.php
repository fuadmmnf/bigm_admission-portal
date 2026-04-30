<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Exams - {{ ucfirst($currentStatus) }}</h2>
            <a href="{{ route('admin.exams.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">Create Exam</a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 flex flex-wrap gap-2">
                <a href="{{ route('admin.exams.draft') }}" class="px-3 py-2 rounded {{ $currentStatus === 'draft' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">Draft</a>
                <a href="{{ route('admin.exams.active') }}" class="px-3 py-2 rounded {{ $currentStatus === 'active' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">Active</a>
                <a href="{{ route('admin.exams.complete') }}" class="px-3 py-2 rounded {{ $currentStatus === 'complete' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">Complete</a>

                <form action="" method="GET" class="ml-auto flex gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search exam..." class="border-gray-300 rounded-md text-sm">
                    <button type="submit" class="px-3 py-2 bg-gray-800 text-white rounded text-sm">Search</button>
                </form>
            </div>

            <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Exam</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Paid</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Start</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">End</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($exams as $exam)
                            <tr class="hover:bg-gray-50/60 transition-colors duration-100">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $exam->name }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">{{ $exam->paid_applications_count }}</span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">{{ optional($exam->start_date)->format('d M Y, h:i A') ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">{{ optional($exam->end_date)->format('d M Y, h:i A') ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a
                                            href="{{ route('admin.exams.reports.index', $exam) }}"
                                            class="inline-flex items-center rounded-md bg-indigo-50 px-2.5 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-100"
                                        >
                                            Reports
                                        </a>
                                        <a
                                            href="{{ route('admin.exams.show', $exam) }}"
                                            class="inline-flex items-center rounded-md bg-sky-50 px-2.5 py-1.5 text-xs font-medium text-sky-700 hover:bg-sky-100"
                                        >
                                            Applicants
                                        </a>
                                        <a
                                            href="{{ route('admin.exams.edit', $exam) }}"
                                            class="inline-flex items-center rounded-md bg-gray-100 px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200"
                                        >
                                            Edit
                                        </a>
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-md bg-rose-50 px-2.5 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-100"
                                            data-delete-trigger
                                            data-delete-url="{{ route('admin.exams.destroy', $exam) }}"
                                            data-exam-name="{{ $exam->name }}"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">No exams found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $exams->links() }}
            </div>
        </div>
    </div>

    <dialog id="delete-exam-modal" class="w-full max-w-md rounded-xl p-0 backdrop:bg-black/30">
        <div class="bg-white rounded-xl border border-gray-200 shadow-xl p-6 space-y-4">
            <h3 class="text-lg font-semibold text-gray-900">Delete Exam</h3>
            <p class="text-sm text-gray-600">
                Are you sure you want to delete <span id="delete-exam-name" class="font-semibold text-gray-900"></span>? This action can be restored only from the database.
            </p>

            <form id="delete-exam-form" method="POST" class="flex items-center justify-end gap-3">
                @csrf
                @method('DELETE')
                <button type="button" id="delete-exam-cancel" class="px-4 py-2 rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-100">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-md bg-red-600 text-sm font-semibold text-white hover:bg-red-700">Delete</button>
            </form>
        </div>
    </dialog>

    <script>
        (() => {
            const modal = document.getElementById('delete-exam-modal');
            const form = document.getElementById('delete-exam-form');
            const examNameTarget = document.getElementById('delete-exam-name');
            const cancelButton = document.getElementById('delete-exam-cancel');

            if (!modal || !form || !examNameTarget || !cancelButton) {
                return;
            }

            document.querySelectorAll('[data-delete-trigger]').forEach((button) => {
                button.addEventListener('click', () => {
                    form.setAttribute('action', button.getAttribute('data-delete-url') || '');
                    examNameTarget.textContent = button.getAttribute('data-exam-name') || 'this exam';
                    modal.showModal();
                });
            });

            cancelButton.addEventListener('click', () => {
                modal.close();
            });
        })();
    </script>
</x-app-layout>

