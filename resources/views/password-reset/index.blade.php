@extends('layout.skeleton')

@section('title', 'Reset Password')

@section('content')
<div class="space-y-6">
    <div class="text-center">
        <h1 class="text-2xl font-bold text-white">Reset your password</h1>
        <p class="mt-2 text-gray-400">Enter your email address and we'll send you a link to reset your password.</p>
    </div>

    @if (session('status'))
        <div class="bg-green-500/10 text-green-500 p-2.5 rounded-lg text-center">
            {{ session('status') }}
        </div>
    @endif

    @error('error')
        <div class="bg-red-500/10 text-red-500 p-2.5 rounded-lg text-center">
            {{ $message }}
        </div>
    @enderror

    <form class="space-y-4" action="{{ route('password.email') }}" method="POST">
        @csrf
        <div>
            <label for="email" class="mb-2 block text-sm font-medium text-gray-300">Email address</label>
            <input type="email" name="email" id="email"
                   class="block w-full rounded-lg border border-gray-600 bg-gray-700 p-2.5 text-white placeholder-gray-400 focus:border-[#4F46E5] focus:ring-[#4F46E5]"
                   placeholder="name@company.com" required>
        </div>

        <button type="submit"
                class="w-full rounded-lg bg-[#4F46E5] px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-[#4338CA] focus:ring-4 focus:ring-[#4F46E5]/50">
            Send Reset Link
        </button>

        <div class="text-center">
            <a href="{{ route('login') }}" class="text-sm text-gray-400 hover:text-white">
                Back to login
            </a>
        </div>
    </form>
</div>
@endsection
