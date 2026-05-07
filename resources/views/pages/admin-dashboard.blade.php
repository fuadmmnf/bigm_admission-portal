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


    <!-- Stats Row -->
    <section class="bg-white border border-gray-200 rounded-lg px-3 py-2 shadow-sm">
        <div class="overflow-x-auto">
            <dl class="min-w-max flex items-center gap-2 text-xs whitespace-nowrap">
                @php
                    $stats = $this->getStats();
                    $statConfig = [
                        'total_exams'       => ['label' => 'Total Exams',       'cls' => 'border-gray-200 bg-gray-50     text-gray-700'],
                        'active_exams'      => ['label' => 'Active Exams',      'cls' => 'border-indigo-200 bg-indigo-50  text-indigo-700'],
                        'draft_exams'       => ['label' => 'Draft Exams',       'cls' => 'border-blue-200 bg-blue-50     text-blue-700'],
                        'paid_applications' => ['label' => 'Paid Applications', 'cls' => 'border-emerald-200 bg-emerald-50 text-emerald-700'],
                    ];
                @endphp
                @foreach ($statConfig as $key => $config)
                    <div class="inline-flex items-center gap-1.5 rounded-md border {{ $config['cls'] }} px-2 py-1">
                        <dt>{{ $config['label'] }}:</dt>
                        <dd class="font-semibold">{{ $stats[$key] ?? 0 }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
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
