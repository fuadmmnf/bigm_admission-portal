<?php

use App\Models\Application;
use App\Models\Exam;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
new class extends Component {
    use WithPagination;

    public string $examFilter = '';
    public string $statusFilter = '';

    public function getExams()
    {
        return Exam::query()
            ->when($this->examFilter, fn ($q) => $q->where('name', 'like', '%'.$this->examFilter.'%'))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->with('category')
            ->paginate(10);
    }

    public function getApplications()
    {
        return Application::query()
            ->with('exam')
            ->latest()
            ->paginate(10);
    }

    public function getStats()
    {
        return [
            'total_exams' => Exam::query()->count(),
            'active_exams' => Exam::query()->where('status', 'active')->count(),
            'total_applications' => Application::query()->count(),
            'pending_applications' => Application::query()->where('status', 'submitted')->count(),
        ];
    }
};
?>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
            <p class="text-gray-600 mt-2">Manage exams and applications</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            @foreach ($this->getStats() as $label => $value)
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-gray-600 text-sm font-medium mb-2">
                        {{ ucfirst(str_replace('_', ' ', $label)) }}
                    </div>
                    <div class="text-2xl font-bold text-indigo-600">
                        {{ $value }}
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Exams Section -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-900">Exams</h2>
                    <a href="#" class="text-sm bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                        Create Exam
                    </a>
                </div>

                <div class="p-6 space-y-4">
                    <!-- Filters -->
                    <div class="space-y-3">
                        <input
                            type="text"
                            wire:model.live="examFilter"
                            placeholder="Search exams..."
                            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                        >
                        <select
                            wire:model.live="statusFilter"
                            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                        >
                            <option value="">All Statuses</option>
                            <option value="draft">Draft</option>
                            <option value="active">Active</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>

                    <!-- Exams List -->
                    <div class="space-y-2">
                        @forelse ($this->getExams() as $exam)
                            <div class="flex justify-between items-center p-4 bg-gray-50 rounded border border-gray-200 hover:bg-gray-100 transition">
                                <div>
                                    <h3 class="font-medium text-gray-900">{{ $exam->name }}</h3>
                                    <p class="text-xs text-gray-600 mt-1">
                                        {{ $exam->category->name }} • Status: <span class="font-semibold text-indigo-600">{{ ucfirst($exam->status) }}</span>
                                    </p>
                                </div>
                                <span class="text-xs bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full">
                                    {{ $exam->applications_count ?? 0 }} apps
                                </span>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500">
                                No exams found
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $this->getExams()->links() }}
                    </div>
                </div>
            </div>

            <!-- Recent Applications Section -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Recent Applications</h2>
                </div>

                <div class="p-6">
                    <div class="space-y-2">
                        @forelse ($this->getApplications() as $application)
                            <div class="flex justify-between items-center p-4 bg-gray-50 rounded border border-gray-200 hover:bg-gray-100 transition">
                                <div>
                                    <h3 class="font-medium text-gray-900">{{ $application->applicant_name }}</h3>
                                    <p class="text-xs text-gray-600 mt-1">
                                        {{ $application->applicant_email }} • {{ $application->exam->name }}
                                    </p>
                                </div>
                                <span class="text-xs px-3 py-1 rounded-full {{ $application->status === 'submitted' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ ucfirst($application->status) }}
                                </span>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500">
                                No applications yet
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $this->getApplications()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

