<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Portal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900">
    <header class="bg-white border-b">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <h1 class="text-xl font-bold text-indigo-700">Admission Portal</h1>
            <a href="{{ route('admin-login') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Admin Login</a>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-10">
        <div class="mb-8">
            <h2 class="text-3xl font-bold mb-2">Active Exams Open for Application</h2>
            <p class="text-gray-600">Only currently active exams are shown to public users.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($exams as $exam)
                <article class="bg-white rounded-lg border border-gray-200 shadow-sm p-5">
                    <div class="flex items-center justify-between mb-2 gap-2">
                        <span class="text-xs font-semibold px-2 py-1 rounded bg-green-100 text-green-700">Active</span>
                        <span class="text-xs text-gray-500 text-right">
                            Apply: {{ optional($exam->start_date)->format('d M Y h:i A') ?? 'Now' }}<br>
                            to {{ optional($exam->end_date)->format('d M Y h:i A') ?? 'Until closed' }}
                        </span>
                    </div>
                    <h3 class="font-semibold text-lg mb-1">{{ $exam->name }}</h3>
                    <p class="text-sm text-gray-700 line-clamp-3">{{ $exam->description ?: 'No details provided yet.' }}</p>

                    <div class="mt-5">
                        <a
                            href="{{ route('applications.create', $exam) }}"
                            class="inline-flex items-center justify-center w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                        >
                            Apply Now
                        </a>
                    </div>
                </article>
            @empty
                <div class="col-span-full bg-white border border-gray-200 rounded-lg p-8 text-center text-gray-500">
                    No active exam is available right now.
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $exams->links() }}
        </div>
    </main>
</body>
</html>

