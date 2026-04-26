<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta http-equiv="refresh" content="6;url={{ route('home') }}">
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="bg-white rounded-xl shadow-md p-10 text-center max-w-md w-full">
        <div class="mb-4 text-red-500">
            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008z" />
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Payment Failed</h1>
        <p class="text-gray-600 mb-1">
            {{ $error ?? session('error', 'Your payment could not be processed. Please try again.') }}
        </p>
        <p class="text-xs text-gray-500 mt-5">Redirecting to homepage in a few seconds...</p>
        <a href="{{ route('home') }}" class="inline-block mt-2 text-sm font-medium text-indigo-600 hover:text-indigo-800">Go now</a>
    </div>

    <script>
        setTimeout(() => {
            window.location.href = @js(route('home'));
        }, 6000);
    </script>
</body>
</html>

