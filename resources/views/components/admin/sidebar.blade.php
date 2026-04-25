<div class="mb-4 pb-4 border-b border-gray-200">
    <p class="text-xs font-semibold tracking-widest text-gray-600 uppercase">Navigation</p>
</div>

<nav class="space-y-1">
    <a href="{{ route('admin-dashboard') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin-dashboard') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
        Dashboard
    </a>
    <a href="{{ route('admin.exams.active') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.exams.active') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
        Active Exams
    </a>
    <a href="{{ route('admin.exams.draft') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.exams.draft') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
        Draft Exams
    </a>
    <a href="{{ route('admin.exams.complete') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.exams.complete') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
        Exam History
    </a>
    <a href="{{ route('admin.reports.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.reports.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
        Reports
    </a>
</nav>

<div class="mt-6 pt-4 border-t border-gray-200">
    <a href="{{ route('admin.exams.create') }}" class="w-full inline-flex items-center justify-center px-3 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
        Create Exam
    </a>
</div>

