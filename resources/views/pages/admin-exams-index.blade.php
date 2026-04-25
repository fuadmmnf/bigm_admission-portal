<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Exams - {{ ucfirst($currentStatus) }}</h2>
            <a href="{{ route('admin.exams.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">Create Exam</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
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
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Category</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Paid</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Unpaid</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($exams as $exam)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $exam->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $exam->category?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-green-700 font-semibold">{{ $exam->paid_applications_count }}</td>
                                <td class="px-4 py-3 text-sm text-amber-700 font-semibold">{{ $exam->unpaid_applications_count }}</td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <a href="{{ route('admin.exams.show', $exam) }}" class="text-indigo-600 hover:text-indigo-800">View</a>
                                    <span class="text-gray-300 px-1">|</span>
                                    <a href="{{ route('admin.exams.edit', $exam) }}" class="text-gray-700 hover:text-gray-900">Edit</a>
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
</x-app-layout>

