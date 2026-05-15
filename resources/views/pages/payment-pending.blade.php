@extends('layouts.app')

@section('content')
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <!-- Loading Spinner -->
                <div class="mb-6">
                    <div class="inline-block">
                        <svg class="animate-spin h-12 w-12 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>

                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    Payment Confirmation in Progress
                </h2>

                <p class="mt-4 text-gray-600">
                    {{ $message ?? 'Your payment is being confirmed. Please wait...' }}
                </p>

                <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-800">
                        <strong>Application ID:</strong> {{ $application ?? 'N/A' }}
                    </p>
                    <p class="text-xs text-blue-700 mt-2">
                        Do not close this page. You will be automatically redirected once confirmation is complete.
                    </p>
                </div>

                <div class="mt-8 space-y-4">
                    <div class="text-sm text-gray-600">
                        <p class="mb-3">If you are not redirected within 30 seconds:</p>
                        <a href="{{ route('home') }}" class="inline-block px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                            Return to Home
                        </a>
                    </div>
                </div>

                <div class="mt-12 pt-8 border-t border-gray-200">
                    <p class="text-xs text-gray-500">
                        <strong>Status:</strong> Pending IPN Confirmation from Payment Gateway
                    </p>
                    <p class="text-xs text-gray-500 mt-2">
                        <strong>What's happening:</strong> The payment gateway is confirming your transaction details.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh after 10 seconds to check if IPN has processed
        setTimeout(() => {
            location.reload();
        }, 10000);

        // Show alternative message if still pending after 60 seconds
        setTimeout(() => {
            const message = document.querySelector('[data-role="pending-message"]');
            if (message) {
                message.innerHTML = 'Your payment is taking longer than expected. Please contact support with your application ID if the issue persists.';
            }
        }, 60000);
    </script>
@endsection

