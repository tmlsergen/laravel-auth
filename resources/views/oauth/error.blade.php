<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authorization Error</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
            <div class="text-center mb-8">
                <svg class="mx-auto h-12 w-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                
                <h2 class="mt-4 text-2xl font-bold text-gray-800">
                    Authorization Error
                </h2>
            </div>

            <div class="text-center">
                <p class="text-gray-600">
                    {{ $error }}
                </p>
            </div>

            <div class="mt-8">
                <a href="{{ url('/') }}" 
                    class="block w-full text-center bg-blue-500 text-white py-2 px-4 rounded-lg 
                           hover:bg-blue-600 transition duration-200">
                    Return to Home
                </a>
            </div>
        </div>
    </div>
</body>
</html>
