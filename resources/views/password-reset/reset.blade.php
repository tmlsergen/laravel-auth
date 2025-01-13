@extends('layout.skeleton')

@section('title', 'Set New Password')

@section('content')
<div class="space-y-6">
    <div class="text-center">
        <h1 class="text-2xl font-bold text-white">Set new password</h1>
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

    <form class="space-y-4" action="{{ route('password.update') }}" method="POST">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="password" class="mb-2 block text-sm font-medium text-gray-300">New Password</label>
            <input type="password" name="password" id="password"
                   class="block w-full rounded-lg border border-gray-600 bg-gray-700 p-2.5 text-white placeholder-gray-400 focus:border-[#4F46E5] focus:ring-[#4F46E5]"
                   placeholder="••••••••" required>
        </div>

        <div>
            <label for="password_confirmation" class="mb-2 block text-sm font-medium text-gray-300">Confirm Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation"
                   class="block w-full rounded-lg border border-gray-600 bg-gray-700 p-2.5 text-white placeholder-gray-400 focus:border-[#4F46E5] focus:ring-[#4F46E5]"
                   placeholder="••••••••" required>
        </div>

        <div class="space-y-2">
            <p class="text-sm text-gray-400">Password must:</p>
            <ul class="text-sm text-gray-400 list-disc list-inside">
                <li>Be at least 8 characters long</li>
                <li>Include at least one uppercase letter</li>
                <li>Include at least one number</li>
                <li>Include at least one special character</li>
            </ul>
        </div>

        <button type="submit"
                class="w-full rounded-lg bg-[#4F46E5] px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-[#4338CA] focus:ring-4 focus:ring-[#4F46E5]/50">
            Reset Password
        </button>
    </form>
</div>
@endsection
