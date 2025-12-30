<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        Please enter your two-factor authentication code or one of your recovery codes.
    </div>

    <form method="POST" action="{{ route('2fa.verify') }}">
        @csrf

        <div>
            <label for="code" class="block font-medium text-sm text-gray-700">Authentication Code</label>
            <input id="code"
                   type="text"
                   name="code"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                   required
                   autofocus>
            @error('code')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end mt-4">
            <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                Back to login
            </a>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                Verify
            </button>
        </div>
    </form>
</x-guest-layout>
