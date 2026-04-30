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
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Unpaid</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($exams as $exam)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $exam->name }}</td>
                                <td class="px-4 py-3 text-sm text-green-700 font-semibold">{{ $exam->paid_applications_count }}</td>
                                <td class="px-4 py-3 text-sm text-amber-700 font-semibold">{{ $exam->unpaid_applications_count }}</td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a
                                            href="{{ route('admin.exams.reports.index', $exam) }}"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-indigo-200 bg-indigo-50 text-indigo-600 transition-colors duration-150 hover:bg-indigo-100 hover:text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:ring-offset-1"
                                            aria-label="Exam settings"
                                            title="Exam settings & reports"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M11.49 3.17a1 1 0 0 0-1.98 0l-.12.95a1 1 0 0 1-.74.84l-.92.24a1 1 0 0 0-.58 1.56l.57.77a1 1 0 0 1 0 1.18l-.57.77a1 1 0 0 0 .58 1.56l.92.24a1 1 0 0 1 .74.84l.12.95a1 1 0 0 0 1.98 0l.12-.95a1 1 0 0 1 .74-.84l.92-.24a1 1 0 0 0 .58-1.56l-.57-.77a1 1 0 0 1 0-1.18l.57-.77a1 1 0 0 0-.58-1.56l-.92-.24a1 1 0 0 1-.74-.84l-.12-.95ZM10.5 11.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5Z" clip-rule="evenodd"/>
                                            </svg>
                                        </a>

                                        <a
                                            href="{{ route('admin.exams.show', $exam) }}"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-sky-200 bg-sky-50/70 text-sky-600 transition-colors duration-150 hover:bg-sky-100 hover:text-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-200 focus:ring-offset-1"
                                            aria-label="View applicants"
                                            title="View applicants"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path d="M10 3c-4.5 0-8 3.6-9 6.8a1 1 0 0 0 0 .4C2 13.4 5.5 17 10 17s8-3.6 9-6.8a1 1 0 0 0 0-.4C18 6.6 14.5 3 10 3Zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8Z"/>
                                                <path d="M10 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/>
                                            </svg>
                                        </a>

                                        <a
                                            href="{{ route('admin.exams.edit', $exam) }}"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-slate-50 text-slate-600 transition-colors duration-150 hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-200 focus:ring-offset-1"
                                            aria-label="Edit exam"
                                            title="Edit exam"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path d="m13.6 2.3 4.1 4.1a1 1 0 0 1 0 1.4l-8.6 8.6a2 2 0 0 1-.9.5l-4.1 1a1 1 0 0 1-1.2-1.2l1-4.1a2 2 0 0 1 .5-.9l8.6-8.6a1 1 0 0 1 1.4 0Z"/>
                                            </svg>
                                        </a>

                                        <button
                                            type="button"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-rose-200 bg-rose-50/70 text-rose-500 transition-colors duration-150 hover:bg-rose-100 hover:text-rose-600 focus:outline-none focus:ring-2 focus:ring-rose-200 focus:ring-offset-1"
                                            aria-label="Delete exam"
                                            title="Delete exam"
                                            data-delete-trigger
                                            data-delete-url="{{ route('admin.exams.destroy', $exam) }}"
                                            data-exam-name="{{ $exam->name }}"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M6 4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2h3a1 1 0 1 1 0 2h-1v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6H3a1 1 0 1 1 0-2h3Zm2 0h4v0H8v0Zm-1 4a1 1 0 0 1 1 1v6a1 1 0 1 1-2 0V9a1 1 0 0 1 1-1Zm6 1a1 1 0 1 0-2 0v6a1 1 0 1 0 2 0V9Z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">No exams found.</td>
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

