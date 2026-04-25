<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $isEdit ? 'Edit Exam' : 'Create Exam' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm border border-gray-200 rounded-lg p-6">
                <form method="POST" action="{{ $isEdit ? route('admin.exams.update', $exam) : route('admin.exams.store') }}" class="space-y-6">
                    @csrf
                    @if($isEdit)
                        @method('PUT')
                    @endif

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Exam Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $exam->name) }}" class="mt-1 block w-full border-gray-300 rounded-md" required>
                        @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                        <select id="category_id" name="category_id" class="mt-1 block w-full border-gray-300 rounded-md" required>
                            <option value="">Select category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) old('category_id', $exam->category_id) === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status" name="status" class="mt-1 block w-full border-gray-300 rounded-md" required>
                            @foreach(['draft' => 'Draft', 'active' => 'Active', 'closed' => 'Complete'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $exam->status ?: 'draft') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input id="start_date" name="start_date" type="datetime-local" value="{{ old('start_date', optional($exam->start_date)->format('Y-m-d\\TH:i')) }}" class="mt-1 block w-full border-gray-300 rounded-md">
                            @error('start_date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                            <input id="end_date" name="end_date" type="datetime-local" value="{{ old('end_date', optional($exam->end_date)->format('Y-m-d\\TH:i')) }}" class="mt-1 block w-full border-gray-300 rounded-md">
                            @error('end_date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="description" name="description" rows="4" class="mt-1 block w-full border-gray-300 rounded-md">{{ old('description', $exam->description) }}</textarea>
                        @error('description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">{{ $isEdit ? 'Update Exam' : 'Create Exam' }}</button>
                        <a href="{{ route('admin.exams.active') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

