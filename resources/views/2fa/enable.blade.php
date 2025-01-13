@extends('layout.skeleton')

@section('content')
    <div class="min-h-screen flex items-center justify-center bg-[#1F2937] p-6 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-white">
                    Enable Two-Factor Authentication
                </h2>
                <p class="mt-2 text-center text-sm text-gray-300">
                    Scan the QR code with your authenticator app
                </p>
            </div>

            <!-- QR Code Section -->
            <div class="flex justify-center mt-6">
                <div class="p-4 bg-gray-800 rounded-lg shadow-md">
                    <img src="data:image/svg+xml;base64,{{ base64_encode($qr) }}"
                         alt="2FA QR Code"
                         class="w-48 h-48">
                </div>
            </div>

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

            <a href="{{ route('2fa.verify') }}"
               class="flex-1 group relative flex justify-center py-2 px-4 border border-gray-600 text-sm font-medium rounded-md text-white bg-gray-700 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Verify
            </a>

            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-600"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-[#1F2937] text-gray-300">
                            Important Notes
                        </span>
                    </div>
                </div>
                <div class="mt-4 text-sm text-gray-300 space-y-2">
                    <p>• Store your backup codes in a secure location</p>
                    <p>• When you use backup codes, your 2fa will be disabled</p>
                    <p>• You'll need these codes if you lose access to your authenticator app</p>
                </div>
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
