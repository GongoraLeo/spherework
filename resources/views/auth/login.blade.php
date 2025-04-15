<x-app-layout>
    {{-- Contenedor principal para centrar y dar padding --}}
    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            {{-- La "tarjeta" o "caja" visual --}}
            <div class="bg-white dark:bg-gray-200 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100"> {{-- Ojo: texto base es claro en dark, pero inputs necesitan ser oscuros --}}

                    <!-- Session Status -->
                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <!-- Email Address -->
                        <div>
                            {{-- Label: Aseguramos que sea oscuro en modo oscuro si el fondo es claro --}}
                            <x-input-label for="email" :value="__('Email')" class="dark:text-gray-700" />
                            {{-- Input: Añadimos dark:text-gray-900 --}}
                            <x-text-input id="email" class="block mt-1 w-full dark:text-gray-900" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Password -->
                        <div class="mt-4">
                             {{-- Label: Aseguramos que sea oscuro en modo oscuro si el fondo es claro --}}
                            <x-input-label for="password" :value="__('Password')" class="dark:text-gray-700" />
                            {{-- Input: Añadimos dark:text-gray-900 --}}
                            <x-text-input id="password" class="block mt-1 w-full dark:text-gray-900"
                                            type="password"
                                            name="password"
                                            required autocomplete="current-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- Remember Me -->
                        <div class="block mt-4">
                            <label for="remember_me" class="inline-flex items-center">
                                <input id="remember_me" type="checkbox" class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-100 dark:focus:ring-offset-gray-200 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600" name="remember">
                                {{-- Span: Aseguramos que sea oscuro en modo oscuro si el fondo es claro --}}
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-700">{{ __('Remember me') }}</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            @if (Route::has('password.request'))
                                {{-- Link: Aseguramos que sea oscuro en modo oscuro si el fondo es claro --}}
                                <a class="underline text-sm text-gray-600 dark:text-gray-700 hover:text-gray-900 dark:hover:text-black rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-200" href="{{ route('password.request') }}">
                                    {{ __('Forgot your password?') }}
                                </a>
                            @endif

                            <x-primary-button class="ms-3">
                                {{ __('Log in') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div> {{-- Cierre de div.p-6 --}}
            </div> {{-- Cierre de div.bg-white --}}
        </div> {{-- Cierre de div.max-w-md --}}
    </div> {{-- Cierre de div.py-12 --}}
</x-app-layout>
