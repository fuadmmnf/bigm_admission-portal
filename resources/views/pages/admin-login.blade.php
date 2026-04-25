<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function authenticate(): void
    {
        $validated = $this->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            $this->addError('email', trans('auth.failed'));

            return;
        }

        if (! $user->hasAnyRole(['admin', 'moderator'])) {
            $this->addError('email', 'Unauthorized access. Admin/Moderator access required.');

            return;
        }

        Auth::login($user, $this->remember);
        session()->regenerate();

        $this->redirect(route('admin-dashboard'));
    }
};
?>

<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
        <!-- Logo / Header -->
        <div class="flex justify-center mb-6">
            <div class="text-2xl font-bold text-indigo-600">Admission Portal</div>
        </div>

        <!-- Form -->
        <form wire:submit.prevent="authenticate" class="space-y-6">
            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <input
                    id="email"
                    type="email"
                    wire:model="email"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="admin@example.com"
                    required
                >
                @error('email')
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input
                    id="password"
                    type="password"
                    wire:model="password"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="••••••••"
                    required
                >
                @error('password')
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="flex items-center">
                <input
                    id="remember"
                    type="checkbox"
                    wire:model="remember"
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                >
                <label for="remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
            </div>

            <!-- Submit -->
            <button
                type="submit"
                class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium transition"
            >
                Sign In
            </button>
        </form>

        <!-- Footer -->
        <div class="mt-6 text-center text-xs text-gray-600">
            Admin/Moderator access only
        </div>
    </div>
</div>

