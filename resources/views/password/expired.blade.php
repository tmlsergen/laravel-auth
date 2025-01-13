@extends('layout.skeleton')

@section('title', 'Reset Password')

@section('content')
    <div class="space-y-6">
        <div class="text-center">
            <h1 class="text-2xl font-bold text-white">Your password has been expired</h1>
            <p class="mt-2 text-gray-400">Please enter your new password below.</p>
        </div>

        @error('error')
        <div class="bg-red-500/10 text-red-500 p-2.5 rounded-lg text-center">
            {{ $message }}
        </div>
        @enderror

        @error('password')
        <div class="bg-red-500/10 text-red-500 p-2.5 rounded-lg text-center">
            {{ $message }}
        </div>
        @enderror

        <form method="POST" action="{{ route('password.expired_update') }}" class="space-y-6">
            @csrf

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
                <label for="password_confirmation" class="block text-lg text-gray-300">Confirm New Password</label>
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
@endsection
