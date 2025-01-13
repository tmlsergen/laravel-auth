<div class="flex items-center justify-between">
    <!-- Logo -->
    <div class="flex items-center">
        <a href="{{ url('/') }}" class="flex items-center gap-3">
            <div class="rounded-full bg-[#4F46E5] p-2">
                <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                </svg>
            </div>
            <span class="text-2xl font-bold text-white">{{ config('app.name') }}</span>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex items-center space-x-4">
        @auth
            <span class="text-sm text-gray-400">{{ auth()->user()->email }}</span>
            <a
                class="rounded-lg bg-gray-600 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 focus:ring-2 focus:ring-gray-500"
                href="{{ route('dashboard') }}">
                Dashboard
            </a>
            <a
                class="rounded-lg bg-gray-600 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 focus:ring-2 focus:ring-gray-500"
                href="{{ route('user.me') }}">
                Profile
            </a>
            <a
                class="rounded-lg bg-gray-600 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 focus:ring-2 focus:ring-gray-500"
                href="{{ route('user.me.sessions') }}">
                Sessions
            </a>
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit"
                        class="rounded-lg bg-gray-600 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 focus:ring-2 focus:ring-gray-500">
                    Sign out
                </button>
            </form>
        @else
            <a href="{{ route('login') }}"
               class="rounded-lg px-4 py-2 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white">
                Sign in
            </a>
        @endauth
    </nav>
</div>
