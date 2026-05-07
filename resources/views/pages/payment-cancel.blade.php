<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Cancelled</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta http-equiv="refresh" content="6;url={{ route('home') }}">
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="bg-white rounded-xl shadow-md p-10 text-center max-w-md w-full">
        <div class="mb-4 text-yellow-500">
            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Payment Cancelled</h1>
        <p class="text-gray-600 mb-1">
            {{ $info ?? session('info', 'You cancelled the payment. You can try again any time before the exam deadline.') }}
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

