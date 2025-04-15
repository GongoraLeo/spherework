<x-app-layout>
    {{-- Contenedor principal para centrar y dar padding (igual que en login) --}}
    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            {{-- La "tarjeta" o "caja" visual (igual que en login) --}}
            <div class="bg-white dark:bg-gray-200 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100"> {{-- Ojo: texto base es claro en dark, pero inputs necesitan ser oscuros --}}

                    {{-- El formulario de registro va aquí dentro --}}
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <!-- Name -->
                        <div>
                            {{-- Label: Aseguramos que sea oscuro en modo oscuro si el fondo es claro --}}
                            <x-input-label for="name" :value="__('Name')" class="dark:text-gray-700" />
                            {{-- Input: Añadimos dark:text-gray-900 --}}
                            <x-text-input id="name" class="block mt-1 w-full dark:text-gray-900" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Email Address -->
                        <div class="mt-4">
                            {{-- Label: Aseguramos que sea oscuro en modo oscuro si el fondo es claro --}}
                            <x-input-label for="email" :value="__('Email')" class="dark:text-gray-700" />
                            {{-- Input: Añadimos dark:text-gray-900 --}}
                            <x-text-input id="email" class="block mt-1 w-full dark:text-gray-900" type="email" name="email" :value="old('email')" required autocomplete="username" />
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
                                            required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- Confirm Password -->
                        <div class="mt-4">
                            {{-- Label: Aseguramos que sea oscuro en modo oscuro si el fondo es claro --}}
                            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="dark:text-gray-700" />
                            {{-- Input: Añadimos dark:text-gray-900 --}}
                            <x-text-input id="password_confirmation" class="block mt-1 w-full dark:text-gray-900"
                                            type="password"
                                            name="password_confirmation" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            {{-- Link: Aseguramos que sea oscuro en modo oscuro si el fondo es claro --}}
                            <a class="underline text-sm text-gray-600 dark:text-gray-700 hover:text-gray-900 dark:hover:text-black rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-200" href="{{ route('login') }}">
                                {{ __('Already registered?') }}
                            </a>

                            <x-primary-button class="ms-4">
                                {{ __('Register') }}
                            </x-primary-button>
                        </div>
                    </form>
                    {{-- Fin del formulario de registro --}}

                </div> {{-- Cierre de div.p-6 --}}
            </div> {{-- Cierre de div.bg-white --}}
        </div> {{-- Cierre de div.max-w-md --}}
    </div> {{-- Cierre de div.py-12 --}}
</x-app-layout>
