<?php

use App\Models\Application;
use App\Models\Exam;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public function getStats()
    {
        return [
            'total_exams' => Exam::query()->count(),
            'active_exams' => Exam::query()->where('status', 'active')->count(),
            'draft_exams' => Exam::query()->where('status', 'draft')->count(),
            'paid_applications' => Application::query()->where('status', 'paid')->count(),
        ];
    }
};
?>

<div class="py-10">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-600 px-6 py-8 mb-6 text-white shadow-lg">
            <h1 class="text-3xl font-bold">Admin Dashboard</h1>
            <p class="mt-2 text-indigo-100">Overview and quick navigation for exam operations.</p>
        </div>

        <div class="flex flex-col md:flex-row gap-6 items-start">
            <aside class="w-full md:w-72 md:shrink-0 bg-white border border-gray-200 rounded-2xl p-4 shadow-sm md:sticky md:top-6">
                <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-3">Sidebar Navigation</h2>
                <nav class="space-y-1.5">
                    <a href="{{ route('admin.exams.active') }}" class="flex items-center px-3 py-2.5 rounded-lg bg-indigo-50 text-indigo-700 font-medium hover:bg-indigo-100 transition">Active Exams</a>
                    <a href="{{ route('admin.exams.draft') }}" class="flex items-center px-3 py-2.5 rounded-lg text-gray-700 hover:bg-gray-100 transition">Draft Exams</a>
                    <a href="{{ route('admin.exams.complete') }}" class="flex items-center px-3 py-2.5 rounded-lg text-gray-700 hover:bg-gray-100 transition">Exam History</a>
                    <a href="{{ route('admin.exams.create') }}" class="flex items-center px-3 py-2.5 rounded-lg text-green-700 bg-green-50 hover:bg-green-100 transition">Create Exam</a>
                    <a href="{{ route('admin.reports.index') }}" class="flex items-center px-3 py-2.5 rounded-lg text-amber-700 bg-amber-50 hover:bg-amber-100 transition">Reports</a>
                </nav>
            </aside>

            <section class="w-full md:flex-1">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @php($labels = [
                        'total_exams' => 'Total Exams',
                        'active_exams' => 'Active Exams',
                        'draft_exams' => 'Draft Exams',
                        'paid_applications' => 'Paid Applicants',
                    ])

                    @php($styles = [
                        'total_exams' => 'from-slate-50 to-slate-100 border-slate-200',
                        'active_exams' => 'from-indigo-50 to-indigo-100 border-indigo-200',
                        'draft_exams' => 'from-blue-50 to-blue-100 border-blue-200',
                        'paid_applications' => 'from-emerald-50 to-emerald-100 border-emerald-200',
                    ])

                    @foreach ($this->getStats() as $key => $value)
                        <div class="rounded-2xl border bg-gradient-to-br {{ $styles[$key] ?? 'from-gray-50 to-gray-100 border-gray-200' }} p-5 shadow-sm">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">{{ $labels[$key] ?? $key }}</p>
                            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</div>

