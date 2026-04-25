<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reports</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900">Reports Center</h3>
                <p class="mt-2 text-sm text-gray-600">Report generation pages (PDF/Excel) will be linked here.</p>

                <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <a href="#" class="px-4 py-3 rounded-md bg-indigo-50 text-indigo-700 text-sm">Application Summary Report</a>
                    <a href="#" class="px-4 py-3 rounded-md bg-indigo-50 text-indigo-700 text-sm">Exam-wise Paid/Unpaid Report</a>
                    <a href="#" class="px-4 py-3 rounded-md bg-indigo-50 text-indigo-700 text-sm">Viva Shortlist Report</a>
                    <a href="#" class="px-4 py-3 rounded-md bg-indigo-50 text-indigo-700 text-sm">Final Selection Export</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

