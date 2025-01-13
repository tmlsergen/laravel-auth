@extends('layout.skeleton')

@section('content')
    <div class="min-h-screen bg-gray-900 flex items-center justify-center py-12">
        @error('error')
        <div class="bg-red-900/50 border border-red-500 rounded-md p-4 mb-6">
            <p class="text-sm text-red-400">{{ $message }}</p>
        @enderror
        <div class="w-full max-w-lg space-y-8">
            @if(!auth()->user()->two_factor_enabled)
                <form action="{{ route('2fa.enable') }}" method="POST">
                    @csrf
                    <button
                        type="submit"
                        class="w-full h-12 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium transition-colors">
                        Enable 2FA
                    </button>
                </form>
            @else
                <a
                    href="{{ route('2fa.backup-codes') }}"
                    class="flex-1 group relative flex justify-center py-2 px-4 border border-gray-600 text-sm font-medium rounded-md text-white bg-gray-700 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Get 2FA Recovery Codes
                </a>

                <form action="{{ route('2fa.regenerate-backup-codes') }}" method="POST">
                    @csrf
                    <button
                        type="submit"
                        class="w-full h-12 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium transition-colors">
                        Regenerate Recovery Codes
                    </button>
                </form>
            @endif

            {{-- Profile Information Form --}}

            <div class="bg-[#1C2632] rounded-2xl p-8">
                <h2 class="text-3xl font-bold text-white mb-8">Profile Information</h2>

                @if (session('success'))
                    <div class="mb-6 p-4 bg-green-900/50 border border-green-500 rounded-md">
                        <p class="text-sm text-green-400">{{ session('success') }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('user.me.update') }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="name" class="block text-lg text-gray-300">Full Name</label>
                        <input type="text"
                               name="name"
                               id="name"
                               value="{{ old('name', $user->name) }}"
                               class="mt-2 w-full h-12 px-4 bg-[#E9EEF4] rounded-xl text-gray-900 placeholder-gray-500"
                               required>
                        @error('name')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-lg text-gray-300">Email Address</label>
                        <input type="email"
                               name="email"
                               id="email"
                               value="{{ $user->email }}"
                               class="mt-2 w-full h-12 px-4 bg-[#E9EEF4] rounded-xl text-gray-900 placeholder-gray-500"
                               disabled
                        >
                        @error('email')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                            class="w-full h-12 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium transition-colors">
                        Update Profile
                    </button>
                </form>
            </div>

            {{-- Password Change Form --}}
            <div class="bg-[#1C2632] rounded-2xl p-8">
                <h2 class="text-3xl font-bold text-white mb-8">Change Password</h2>

                @if (session('password_status'))
                    <div class="mb-6 p-4 bg-green-900/50 border border-green-500 rounded-md">
                        <p class="text-sm text-green-400">{{ session('password_status') }}</p>
                    </div>
                @endif

                @error('password_error')
                <div class="mb-6 p-4 bg-red-900/50 border border-red-500 rounded-md">
                    <p class="text-sm text-red-400">{{ $message }}</p>
                </div>
                @enderror
                <form method="POST" action="{{ route('user.me.password') }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password" class="block text-lg text-gray-300">Current Password</label>
                        <input type="password"
                               name="current_password"
                               id="current_password"
                               class="mt-2 w-full h-12 px-4 bg-[#E9EEF4] rounded-xl text-gray-900 placeholder-gray-500"
                               required>
                        @error('current_password')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-lg text-gray-300">New Password</label>
                        <input type="password"
                               name="password"
                               id="password"
                               class="mt-2 w-full h-12 px-4 bg-[#E9EEF4] rounded-xl text-gray-900 placeholder-gray-500"
                               required>
                        @error('password')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-lg text-gray-300">Confirm New
                            Password</label>
                        <input type="password"
                               name="password_confirmation"
                               id="password_confirmation"
                               class="mt-2 w-full h-12 px-4 bg-[#E9EEF4] rounded-xl text-gray-900 placeholder-gray-500"
                               required>
                    </div>

                    <button type="submit"
                            class="w-full h-12 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium transition-colors">
                        Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
