@extends('layout.skeleton')

@section('title', 'Welcome to ' . config('app.name'))

@section('content')
<div class="space-y-6 text-center">
    <div class="space-y-2">
        <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
            Welcome to {{ config('app.name') }}
        </h1>
        <p class="text-lg text-gray-400">
            Your secure authentication solution
        </p>
    </div>

    <div class="flex justify-center gap-4">
        <a href="{{ route('login') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-[#4F46E5] px-6 py-3 text-sm font-semibold text-white hover:bg-[#4338CA] focus:ring-2 focus:ring-[#4F46E5]/50">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M15 3H7C5.89543 3 5 3.89543 5 5V19C5 20.1046 5.89543 21 7 21H15C16.1046 21 17 20.1046 17 19V5C17 3.89543 16.1046 3 15 3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M9 11H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M16 8L19 11L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Sign in to your account
        </a>
    </div>

    <div class="flex justify-center gap-4">
        <a href="{{ route('register') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-[#4F46E5] px-6 py-3 text-sm font-semibold text-white hover:bg-[#4338CA] focus:ring-2 focus:ring-[#4F46E5]/50">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M15 3H7C5.89543 3 5 3.89543 5 5V19C5 20.1046 5.89543 21 7 21H15C16.1046 21 17 20.1046 17 19V5C17 3.89543 16.1046 3 15 3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M9 11H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M16 8L19 11L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Sign up for an account
        </a>
    </div>

    <div class="mt-8 text-sm text-gray-400">
        <p>Built with Laravel and Tailwind CSS</p>
    </div>
</div>
@endsection
