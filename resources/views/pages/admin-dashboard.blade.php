<?php

use App\Models\Application;
use App\Models\Exam;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public function getStats(): array
    {
        return [
            'total_exams' => Exam::query()->count(),
            'active_exams' => Exam::query()->where('status', 'active')->count(),
            'draft_exams' => Exam::query()->where('status', 'draft')->count(),
            'paid_applications' => Application::query()->where('status', 'paid')->count(),
        ];
    }

    public function getRecentExams()
    {
        return Exam::query()->latest()->take(5)->get();
    }

    public function getRecentPaidApplications()
    {
        return Application::query()
            ->where('status', 'paid')
            ->latest()
            ->take(5)
            ->get();
    }
};
?>

<div class="space-y-6">
    <section class="rounded-2xl bg-white border border-gray-200 p-6 shadow-sm">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Dashboard Overview</h1>
        <p class="mt-2 text-sm text-gray-600">Quick summary of exams and application payment activity.</p>
    </section>


    <!-- Stats Cards -->
    <section class="grid grid-cols-1 md:grid-cols-2 md:gap-6">
        @php($labels = [
            'total_exams' => 'Total Exams',
            'active_exams' => 'Active Exams',
            'draft_exams' => 'Draft Exams',
            'paid_applications' => 'Paid Applications',
        ])
        @php($styles = [
            'total_exams' => 'bg-slate-50 border-slate-200',
            'active_exams' => 'bg-indigo-50 border-indigo-200',
            'draft_exams' => 'bg-blue-50 border-blue-200',
            'paid_applications' => 'bg-emerald-50 border-emerald-200',
        ])

        @foreach ($this->getStats() as $key => $value)
            <article class="rounded-xl border {{ $styles[$key] ?? 'bg-gray-50 border-gray-200' }} p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-600">{{ $labels[$key] ?? $key }}</p>
                <p class="mt-3 text-3xl font-bold text-gray-900">{{ $value }}</p>
            </article>
        @endforeach
    </section>

    <!-- Recent Items Section -->
    <section class="grid grid-cols-1 md:grid-cols-2 md:gap-6">
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900">Recent Exams</h3>
                <a href="{{ route('admin.exams.active') }}" class="text-sm text-indigo-600 hover:text-indigo-800">View all</a>
            </div>
            <div class="p-5 space-y-3">
                @forelse($this->getRecentExams() as $exam)
                    <div class="rounded-lg border border-gray-100 p-3 bg-gray-50">
                        <p class="font-medium text-gray-900">{{ $exam->name }}</p>
                        <p class="text-xs text-gray-600 mt-1">Status: {{ ucfirst($exam->status === 'closed' ? 'complete' : $exam->status) }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No exams available.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900">Recent Paid Applications</h3>
                <a href="{{ route('admin.reports.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">Open reports</a>
            </div>
            <div class="p-5 space-y-3">
                @forelse($this->getRecentPaidApplications() as $application)
                    <div class="rounded-lg border border-emerald-100 p-3 bg-emerald-50">
                        <p class="font-medium text-gray-900">{{ $application->applicant_name }}</p>
                        <p class="text-xs text-gray-600 mt-1">{{ $application->applicant_email }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No paid applications yet.</p>
                @endforelse
            </div>
        </div>
    </section>
</div>
