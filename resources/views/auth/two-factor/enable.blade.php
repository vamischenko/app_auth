<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Enable Two-Factor Authentication') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-4">
                            Two-factor authentication adds an additional layer of security to your account.
                            Scan the QR code below with your authenticator app (Google Authenticator, Authy, etc.).
                        </p>
                    </div>

                    <div class="mb-6 text-center">
                        <div class="inline-block p-4 bg-white border-2 border-gray-300 rounded-lg">
                            <img src="{{ $qrCodeUrl }}" alt="QR Code">
                        </div>
                        <p class="mt-2 text-xs text-gray-500">Or enter this code manually: <strong>{{ $secret }}</strong></p>
                    </div>

                    <form method="POST" action="{{ route('2fa.enable.post') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="code" class="block text-sm font-medium text-gray-700">
                                Enter the 6-digit code from your authenticator app
                            </label>
                            <input type="text"
                                   name="code"
                                   id="code"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   maxlength="6"
                                   pattern="[0-9]{6}"
                                   required
                                   autofocus>
                            @error('code')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end">
                            <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Verify & Enable
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
