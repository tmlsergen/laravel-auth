<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authorize Application</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-800">
                    Authorization Request
                </h2>
                <p class="text-gray-600 mt-2">
                    Application would like to access your account
                </p>
            </div>

            <div class="border-t border-b border-gray-200 py-4 mb-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">
                    This application will be able to:
                </h3>
                <ul class="space-y-2">
                    @foreach($scopes as $scope)
                        <li class="flex items-start">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-gray-600">{{ ucfirst($scope) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="flex space-x-4">
                <form method="POST" action="{{ url('/oauth/authorize') }}" class="flex-1">
                    @csrf
                    <input type="hidden" name="client_id" value="{{ $client_id }}">
                    <input type="hidden" name="redirect_uri" value="{{ $redirect_uri }}">
                    <input type="hidden" name="scopes" value="{{ implode(' ', $scopes) }}">
                    <input type="hidden" name="state" value="{{ $state }}">

                    <button type="submit" name="approve" value="1"
                        class="w-full bg-green-500 text-white py-2 px-4 rounded-lg
                               hover:bg-green-600 transition duration-200">
                        Approve
                    </button>
                </form>

                <form method="POST" action="{{ url('/oauth/authorize') }}" class="flex-1">
                    @csrf
                    <input type="hidden" name="client_id" value="{{ $client_id }}">
                    <input type="hidden" name="redirect_uri" value="{{ $redirect_uri }}">
                    <input type="hidden" name="scopes" value="{{ implode(' ', $scopes) }}">
                    <input type="hidden" name="state" value="{{ $state }}">

                    <button type="submit" name="approve" value="0"
                        class="w-full bg-gray-200 text-gray-800 py-2 px-4 rounded-lg
                               hover:bg-gray-300 transition duration-200">
                        Deny
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
