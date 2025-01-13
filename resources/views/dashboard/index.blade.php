@extends('layout.skeleton')

@section('title', 'Dashboard - ' . config('app.name'))

@section('content')
<div class="space-y-6">
    <!-- Welcome Section -->
    <div>
        <h1 class="text-2xl font-bold text-white">Dashboard</h1>
        <p class="text-gray-400">Welcome back, {{ auth()->user()->name }}</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <!-- Total Logins -->
        <div class="rounded-lg border border-gray-700 bg-gray-800 p-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-medium text-gray-400">Total Logins</h3>
                <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
            </div>
            <p class="mt-2 text-3xl font-bold text-white">{{ auth()->user()->login_count ?? 0 }}</p>
        </div>

        <!-- Last Login -->
        <div class="rounded-lg border border-gray-700 bg-gray-800 p-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-medium text-gray-400">Last Login</h3>
                <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="mt-2 text-3xl font-bold text-white">
                {{ auth()->user()->last_login_at ? auth()->user()->last_login_at->diffForHumans() : 'Never' }}
            </p>
        </div>

        <!-- Account Created -->
        <div class="rounded-lg border border-gray-700 bg-gray-800 p-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-medium text-gray-400">Account Created</h3>
                <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <p class="mt-2 text-3xl font-bold text-white">
                {{ auth()->user()->created_at->format('M d, Y') }}
            </p>
        </div>
    </div>

    <!-- Profile Section -->
    <div class="rounded-lg border border-gray-700 bg-gray-800 p-6">
        <h2 class="text-lg font-medium text-white">Profile Information</h2>
        <div class="mt-4 space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-400">Name</label>
                    <p class="mt-1 text-white">{{ auth()->user()->name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400">Email</label>
                    <p class="mt-1 text-white">{{ auth()->user()->email }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
