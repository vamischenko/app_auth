<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Welcome, {{ Auth::user()->name }}!</h3>
                        <p class="text-gray-600">{{ __("You're logged in!") }}</p>
                    </div>

                    @if (session('status'))
                        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="border-t border-gray-200 pt-4 mt-4">
                        <h4 class="font-semibold mb-3">Security Settings</h4>

                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium">Two-Factor Authentication</p>
                                <p class="text-sm text-gray-600">
                                    @if(Auth::user()->google2fa_enabled)
                                        <span class="text-green-600">Enabled</span> - Your account is protected with 2FA
                                    @else
                                        <span class="text-orange-600">Disabled</span> - Add an extra layer of security
                                    @endif
                                </p>
                            </div>
                            <div>
                                @if(Auth::user()->google2fa_enabled)
                                    <form method="POST" action="{{ route('2fa.disable') }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="password" value="">
                                        <button type="button" onclick="if(confirm('Are you sure you want to disable 2FA? Enter your password.')) { const pwd = prompt('Enter your password:'); if(pwd) { this.previousElementSibling.value = pwd; this.form.submit(); } }"
                                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm">
                                            Disable 2FA
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('2fa.enable') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">
                                        Enable 2FA
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if(Auth::user()->oauth_provider)
                        <div class="border-t border-gray-200 pt-4 mt-4">
                            <h4 class="font-semibold mb-3">Connected Accounts</h4>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm">
                                    <span class="font-medium">Provider:</span>
                                    <span class="capitalize">{{ Auth::user()->oauth_provider }}</span>
                                </p>
                                @if(Auth::user()->avatar)
                                    <img src="{{ Auth::user()->avatar }}" alt="Avatar" class="w-12 h-12 rounded-full mt-2">
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
