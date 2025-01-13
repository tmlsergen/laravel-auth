@extends('layout.skeleton')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-[#1F2937] py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-white">
                Two-Factor Authentication
            </h2>
            <p class="mt-2 text-center text-sm text-gray-300">
                Please enter the verification code from your authenticator app
            </p>
        </div>

        @if ($errors->any())
            <div class="rounded-md bg-red-900/50 p-4 mt-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-200">
                            Invalid verification code
                        </h3>
                    </div>
                </div>
            </div>
        @endif

        <form class="mt-8 space-y-6" action="{{ route('2fa.verify') }}" method="POST">
            @csrf
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="code" class="sr-only">Verification Code</label>
                    <input id="code" name="code" type="text" required
                           class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-600 bg-gray-700 placeholder-gray-400 text-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                           placeholder="Enter 6-digit code"
                           pattern="[0-9]*"
                           inputmode="numeric"
                           autocomplete="one-time-code"
                           maxlength="6">
                </div>
            </div>

            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Verify
                </button>
            </div>
        </form>

        <div class="text-sm text-center">
            <a href="{{ route('2fa.backup') }}" class="text-indigo-400 hover:text-indigo-300">
                Did you lose your device?
            </a>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-focus the input field
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('code').focus();
    });

    // Auto-submit when 6 digits are entered
    document.getElementById('code').addEventListener('input', function(e) {
        if (this.value.length === 6) {
            this.form.submit();
        }
    });
</script>
@endpush