@extends('layout.skeleton')

@section('content')
    <!-- Backup Codes Section -->
    <div class="mt-6 bg-gray-800 shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-white mb-4">Backup Codes</h3>
        <p class="text-sm text-gray-300 mb-4">
            Save these backup codes in a secure place. You can use these codes to access your account if you lose your authenticator device.
        </p>
        <div class="grid grid-cols-2 gap-4">
            @foreach($backUpCodes as $code)
                <div class="bg-gray-700 p-2 rounded text-center font-mono text-xs text-gray-200">
                    {{ $code }}
                </div>
            @endforeach
        </div>
    </div>

    <div>
        <a class="flex-1 group relative flex justify-center py-2 px-4 border border-gray-600 text-sm font-medium rounded-md text-white bg-gray-700 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('user.me') }}">Return</a>
    </div>
@endsection
