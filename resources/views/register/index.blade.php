@extends('layout.skeleton')

@section('title', 'Sign up')

@section('content')
    <div class="space-y-6">
        <div class="text-center">
            <h1 class="text-2xl font-bold text-white">Sign up</h1>
        </div>
        @if (session('status'))
            <div class="bg-green-500 text-white p-2.5 rounded-lg text-center">
                {{ session('status') }}
            </div>
        @endif

        @error('error')
        <div class="bg-red-500 text-white p-2.5 rounded-lg text-center">
            {{ $message }}
        </div>
        @enderror

        <form class="space-y-4" action="{{ route('register.post') }}" method="POST">
            @csrf
            <div>
                @error('name')
                <span class="text-red-500">{{ $message }}</span>
                @enderror
                <label for="email" class="mb-2 block text-sm font-medium text-gray-300">Your Name</label>
                <input type="text" name="name" id="name"
                       class="block w-full rounded-lg border border-gray-600 bg-gray-700 p-2.5 text-white placeholder-gray-400 focus:border-[#4F46E5] focus:ring-[#4F46E5]"
                       placeholder="John Doe" required>
            </div>
            <div>
                @error('email')
                <span class="text-red-500">{{ $message }}</span>
                @enderror
                <label for="email" class="mb-2 block text-sm font-medium text-gray-300">Your email</label>
                <input type="email" name="email" id="email"
                       class="block w-full rounded-lg border border-gray-600 bg-gray-700 p-2.5 text-white placeholder-gray-400 focus:border-[#4F46E5] focus:ring-[#4F46E5]"
                       placeholder="name@company.com" required>
            </div>

            <div>
                @error('password')
                <span class="text-red-500">{{ $message }}</span>
                @enderror
                <label for="password" class="mb-2 block text-sm font-medium text-gray-300">Password</label>
                <input type="password" name="password" id="password"
                       class="block w-full rounded-lg border border-gray-600 bg-gray-700 p-2.5 text-white placeholder-gray-400 focus:border-[#4F46E5] focus:ring-[#4F46E5]"
                       placeholder="••••••••" required>
            </div>

            <div>
                <label for="password_confirmation" class="mb-2 block text-sm font-medium text-gray-300">Password Confirmation</label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                       class="block w-full rounded-lg border border-gray-600 bg-gray-700 p-2.5 text-white placeholder-gray-400 focus:border-[#4F46E5] focus:ring-[#4F46E5]"
                       placeholder="••••••••" required>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('login') }}" class="text-sm text-[#4F46E5] hover:underline">
                    Already have an account? Sign in
                </a>
            </div>

            <button type="submit"
                    class="w-full rounded-lg bg-[#4F46E5] px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-[#4338CA] focus:ring-4 focus:ring-[#4F46E5]/50">
                Sign in
            </button>
        </form>
    </div>
@endsection
