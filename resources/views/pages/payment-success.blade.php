<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="bg-white rounded-xl shadow-md p-10 text-center max-w-md w-full">
        <div class="mb-4 text-green-500">
            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Payment Successful</h1>
        <p class="text-gray-600 mb-1">Your payment has been confirmed and your application is submitted.</p>
        @if(session('info'))
            <p class="text-blue-600 text-sm mt-2">{{ session('info') }}</p>
        @endif
        @isset($application)
            <p class="text-xs text-gray-400 mt-4">Application ID: <span class="font-mono">{{ $application }}</span></p>
        @endisset
    </div>
</body>
</html>

