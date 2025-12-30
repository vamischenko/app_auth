<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Two-Factor Recovery Codes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-sm text-green-800 font-semibold">
                            Two-factor authentication has been enabled successfully!
                        </p>
                    </div>

                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-4">
                            Store these recovery codes in a secure place. They can be used to access your account if you lose your authenticator device.
                            Each code can only be used once.
                        </p>
                    </div>

                    <div class="mb-6 p-4 bg-gray-100 rounded-lg">
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($recoveryCodes as $code)
                                <div class="font-mono text-sm">{{ $code }}</div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center justify-end">
                        <button onclick="copyRecoveryCodes()" class="mr-4 inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                            Copy Codes
                        </button>
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Continue to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyRecoveryCodes() {
            const codes = @json($recoveryCodes);
            navigator.clipboard.writeText(codes.join('\n'));
            alert('Recovery codes copied to clipboard!');
        }
    </script>
</x-app-layout>
