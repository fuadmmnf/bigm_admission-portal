<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $exam->name }}</h2>
            <div class="space-x-2">
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

            <div class="bg-white shadow-sm border border-gray-200 rounded-lg p-6">
                <dl class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Status</dt>
                        <dd class="font-semibold text-gray-900">{{ $exam->status === 'closed' ? 'complete' : $exam->status }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Category</dt>
                        <dd class="font-semibold text-gray-900">{{ $exam->category?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Application Window</dt>
                        <dd class="font-semibold text-gray-900">
                            {{ optional($exam->start_date)->format('d M Y') ?? 'N/A' }} - {{ optional($exam->end_date)->format('d M Y') ?? 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Applicants</dt>
                        <dd class="font-semibold text-gray-900">{{ $applications->total() }}</dd>
                    </div>
                </dl>
            </div>

            <section class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-900">Applicant List</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Name</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Phone</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($applications as $application)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $application->applicant_name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $application->applicant_email }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $application->applicant_phone }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ ucfirst($application->status) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">No applicants available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-4">
                    {{ $applications->links() }}
                </div>
            </section>
        </div>
    </div>
</x-app-layout>

