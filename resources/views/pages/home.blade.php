<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Portal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen flex flex-col">
    <header class="bg-white border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <h1 class="text-xl font-bold text-indigo-700">Admission Portal</h1>
            <a href="{{ route('admin-login') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Admin Login</a>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-8 md:py-10 flex-1 flex flex-col">
        <div class="mb-6 md:mb-8 text-center">
            <h2 class="text-3xl font-bold mb-2 text-indigo-800">Bangladesh Institute of Governance and Management (BIGM) Admission Portal</h2>
            <p class="text-gray-600">Current admission notices and applications for BIGM academic programs.</p>
        </div>

        <section class="flex-1 flex items-center justify-center">

        @php $exam = $exams->first(); @endphp

        @if ($exam)
            @php
                $now = now();
                $withinWindow = ($exam->start_date === null || $now->gte($exam->start_date))
                             && ($exam->end_date === null || $now->lte($exam->end_date));
                $hasBrochure = filled($exam->brochure_path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($exam->brochure_path);
                $hasCircular = filled($exam->circular_path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($exam->circular_path);
            @endphp

            <article class="mx-auto w-full max-w-xl bg-white rounded-xl border border-gray-200 shadow-lg overflow-hidden">

                <div class="px-6 pt-4 pb-2 border-b border-gray-100 bg-gray-50/60">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                        @if ($withinWindow)
                            <span class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 border border-emerald-200">Applications Open</span>
                        @else
                            <span class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 border border-amber-200">Not Yet Open</span>
                        @endif
                        <span class="text-xs text-gray-600 text-right">
                            <b>Apply:</b> {{ optional($exam->start_date)->format('d M Y') ?? 'Now' }} - {{ optional($exam->end_date)->format('d M Y') ?? 'Until closed' }}
                        </span>
                    </div>
                    <h3 class="text-xl font-bold leading-snug text-gray-900">{{ $exam->name }}</h3>
                </div>

                <div class="p-6 space-y-5">
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $exam->description ?: 'No details provided yet.' }}</p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        @if ($hasBrochure)
                            <a href="{{ route('public-media.show', ['path' => ltrim($exam->brochure_path, '/')]) }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-md border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100">View Brochure</a>
                        @else
                            <button type="button" disabled class="inline-flex items-center justify-center rounded-md border border-gray-200 bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-400 cursor-not-allowed">Brochure Not Uploaded</button>
                        @endif

                        @if ($hasCircular)
                            <a href="{{ route('public-media.show', ['path' => ltrim($exam->circular_path, '/')]) }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-100">View Circular</a>
                        @else
                            <button type="button" disabled class="inline-flex items-center justify-center rounded-md border border-gray-200 bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-400 cursor-not-allowed">Circular Not Uploaded</button>
                        @endif

                        @if ($withinWindow)
                            <a href="{{ route('applications.create', $exam) }}" class="inline-flex items-center justify-center w-full rounded-md bg-indigo-600 px-4 py-3 text-sm font-bold text-white hover:bg-indigo-700">
                                Apply Now
                            </a>
                        @else
                            <button disabled class="inline-flex items-center justify-center w-full rounded-md bg-gray-200 px-4 py-3 text-sm font-bold text-gray-400 cursor-not-allowed">
                                Applications Not Yet Open
                            </button>
                        @endif
                    </div>
                </div>
            </article>
        @else
            <div class="mx-auto w-full max-w-lg bg-white border border-gray-200 rounded-lg p-8 text-center text-gray-500">
                No active exam is available right now.
            </div>
        @endif
        </section>
    </main>
</body>
</html>

