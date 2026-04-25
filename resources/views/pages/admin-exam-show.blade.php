<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $exam->name }}</h2>
            <div class="space-x-2">
                <a href="{{ route('admin.exams.edit', $exam) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-black">Edit</a>
                <a href="{{ route('admin.exams.active') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest">Back</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm border border-gray-200 rounded-lg p-6">
                <dl class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
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
                </dl>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <section class="bg-white shadow-sm border border-gray-200 rounded-lg">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h3 class="font-semibold text-green-700">Paid Applicants</h3>
                    </div>
                    <div class="p-4 space-y-3">
                        @forelse ($paidApplications as $application)
                            <div class="rounded border border-green-200 bg-green-50 px-3 py-2">
                                <div class="font-medium text-sm text-gray-900">{{ $application->applicant_name }}</div>
                                <div class="text-xs text-gray-600">{{ $application->applicant_email }} • {{ $application->applicant_phone }}</div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No paid applicants yet.</p>
                        @endforelse
                    </div>
                    <div class="px-4 pb-4">
                        {{ $paidApplications->links() }}
                    </div>
                </section>

                <section class="bg-white shadow-sm border border-gray-200 rounded-lg">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h3 class="font-semibold text-amber-700">Unpaid Applicants</h3>
                    </div>
                    <div class="p-4 space-y-3">
                        @forelse ($unpaidApplications as $application)
                            <div class="rounded border border-amber-200 bg-amber-50 px-3 py-2">
                                <div class="font-medium text-sm text-gray-900">{{ $application->applicant_name }}</div>
                                <div class="text-xs text-gray-600">{{ $application->applicant_email }} • {{ $application->applicant_phone }}</div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No unpaid applicants yet.</p>
                        @endforelse
                    </div>
                    <div class="px-4 pb-4">
                        {{ $unpaidApplications->links() }}
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>

