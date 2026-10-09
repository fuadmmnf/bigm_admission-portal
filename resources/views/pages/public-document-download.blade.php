<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Documents - BIGM Admission Portal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 py-8 px-4">
    <div class="max-w-md w-full mx-auto">
        <div class="bg-white rounded-xl shadow-md p-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-800 text-center">Download Documents</h1>
                <p class="text-gray-600 text-center text-sm mt-2">Enter your email and NID to download your CV and admit card</p>
            </div>

            <!-- Form Container -->
            <div id="formContainer">
                <form id="downloadForm" class="space-y-4">
                    @csrf

                    <!-- Exam Selection -->
                    <div>
                        <label for="exam_id" class="block text-sm font-medium text-gray-700 mb-1">Select Exam *</label>
                        <select id="exam_id" name="exam_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Choose an exam --</option>
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                            @endforeach
                        </select>
                        <span class="text-red-500 text-sm hidden" id="exam_id-error"></span>
                    </div>

                    <!-- Email Input -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                        <input type="email" id="email" name="email" required placeholder="your@email.com" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <span class="text-red-500 text-sm hidden" id="email-error"></span>
                    </div>

                    <!-- NID Input -->
                    <div>
                        <label for="nid" class="block text-sm font-medium text-gray-700 mb-1">National ID (NID) *</label>
                        <input type="text" id="nid" name="nid" required placeholder="Your NID number" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <span class="text-red-500 text-sm hidden" id="nid-error"></span>
                    </div>

                    <!-- Error Alert -->
                    <div id="errorAlert" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm"></div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition">
                        <span id="submitText">Search Application</span>
                        <span id="submitSpinner" class="hidden">
                            <svg class="animate-spin h-5 w-5 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Searching...
                        </span>
                    </button>
                </form>
            </div>

            <!-- Results Container (Hidden by default) -->
            <div id="resultsContainer" class="hidden">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center mb-2">
                        <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-green-700 font-medium">Application Found!</span>
                    </div>
                    <p id="applicantInfo" class="text-green-600 text-sm"></p>
                </div>

                <div id="downloadButtons" class="space-y-3">
                    <button type="button" id="downloadCVBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Download CV</span>
                    </button>
                    <button type="button" id="downloadAdmitCardBtn" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-lg transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Download Admit Card</span>
                    </button>
                </div>

                <button type="button" id="searchAgainBtn" class="w-full mt-4 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-lg transition">
                    Search Again
                </button>
            </div>

            <!-- Help Text -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <p class="text-xs text-gray-500 text-center">
                    <strong>Need help?</strong> If you're unable to download your documents, please contact the admission office.
                </p>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('downloadForm');
        const formContainer = document.getElementById('formContainer');
        const resultsContainer = document.getElementById('resultsContainer');
        const errorAlert = document.getElementById('errorAlert');
        const submitBtn = document.querySelector('button[type="submit"]');
        const submitText = document.getElementById('submitText');
        const submitSpinner = document.getElementById('submitSpinner');
        const downloadCVBtn = document.getElementById('downloadCVBtn');
        const downloadAdmitCardBtn = document.getElementById('downloadAdmitCardBtn');
        const searchAgainBtn = document.getElementById('searchAgainBtn');
        const examSelect = document.getElementById('exam_id');

        // Auto-select exam if only one available
        if (examSelect.children.length === 2) { // 1 placeholder + 1 exam
            examSelect.value = examSelect.children[1].value;
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            errorAlert.classList.add('hidden');
            clearFieldErrors();

            submitBtn.disabled = true;
            submitText.classList.add('hidden');
            submitSpinner.classList.remove('hidden');

            const formData = new FormData(form);
            const data = {
                exam_id: formData.get('exam_id'),
                email: formData.get('email'),
                nid: formData.get('nid'),
            };

            try {
                const response = await fetch('{{ route("documents.search") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    },
                    body: JSON.stringify(data),
                });

                const result = await response.json();

                if (response.ok && result.found) {
                    // Store form data for downloads
                    window.currentFormData = data;
                    
                    // Show results
                    const applicantInfo = `${result.application.applicant_name} (App ID: ${result.application.application_id})`;
                    document.getElementById('applicantInfo').textContent = applicantInfo;
                    
                    formContainer.classList.add('hidden');
                    resultsContainer.classList.remove('hidden');
                } else {
                    showError(result.message || 'No application found with the provided information.');
                }
            } catch (error) {
                showError('An error occurred. Please try again.');
                console.error('Error:', error);
            } finally {
                submitBtn.disabled = false;
                submitText.classList.remove('hidden');
                submitSpinner.classList.add('hidden');
            }
        });

        downloadCVBtn.addEventListener('click', () => downloadDocument('cv'));
        downloadAdmitCardBtn.addEventListener('click', () => downloadDocument('admit-card'));

        function downloadDocument(type) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = type === 'cv' ? '{{ route("documents.cv") }}' : '{{ route("documents.admit-card") }}';
            
            const fields = [
                { name: '_token', value: document.querySelector('input[name="_token"]').value },
                { name: 'exam_id', value: window.currentFormData.exam_id },
                { name: 'email', value: window.currentFormData.email },
                { name: 'nid', value: window.currentFormData.nid },
            ];

            fields.forEach(field => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = field.name;
                input.value = field.value;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }

        searchAgainBtn.addEventListener('click', () => {
            form.reset();
            clearFieldErrors();
            errorAlert.classList.add('hidden');
            formContainer.classList.remove('hidden');
            resultsContainer.classList.add('hidden');
            
            // Auto-select exam again if applicable
            if (examSelect.children.length === 2) {
                examSelect.value = examSelect.children[1].value;
            }
        });

        function showError(message) {
            errorAlert.textContent = message;
            errorAlert.classList.remove('hidden');
        }

        function clearFieldErrors() {
            document.querySelectorAll('[id$="-error"]').forEach(el => {
                el.classList.add('hidden');
            });
        }
    </script>
</body>
</html>
