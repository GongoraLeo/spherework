{{-- filepath: resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SphereWork')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles & Scripts (Using Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Additional Page Specific Styles -->
    @stack('styles')

</head>
<body class="font-sans antialiased bg-gray-100 text-gray-800 min-h-screen flex flex-col">

    <!-- TU Navigation Bar Personalizada (Azul) -->
    <nav class="bg-blue-700 shadow-md">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo/Brand -->
                <div class="flex-shrink-0">
                    <a href="{{ url('/') }}" class="text-white text-xl font-bold hover:text-gray-200">
                        SphereWork
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden md:flex items-center space-x-4">
                    <a href="{{ route('libros.index') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Libros</a>
                    {{-- Autores (Visible solo si NO es cliente logueado) --}}
                    @auth
                        {{-- Verifica si el rol NO es 'cliente'. Ajusta 'cliente' si tu rol se llama diferente. --}}
                        {{-- Puedes usar in_array si tienes varios roles permitidos: @if(in_array(Auth::user()->rol, ['empleado', 'administrador'])) --}}
                        @if(Auth::user()->rol != 'cliente')
                            <a href="{{ route('autores.index') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Autores</a>
                        @endif
                    @endauth
                    {{-- Fin Condición Autores --}}

                    {{-- Add other common links --}}

                    {{-- Authentication Links --}}
                    @guest
                        {{-- Muestra estos enlaces SOLO si el usuario NO está logueado --}}
                        <a href="{{ route('login') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('login') ? 'bg-blue-800' : '' }}">Login</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('register') ? 'bg-blue-800' : '' }}">Register</a>
                        @endif
                    @else
                        {{-- Muestra estos enlaces SOLO si el usuario SÍ está logueado --}}
                        {{-- Asumiendo que tienes una ruta 'profile.edit' o similar definida por Breeze/Jetstream --}}
                        <a href="{{ route('profile.edit') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Perfil</a>

                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                                Logout ({{ Auth::user()->name }}) {{-- Esto está bien aquí porque está dentro de @else (que implica @auth) --}}
                            </button>
                        </form>
                    @endguest
                </div>

                <!-- Mobile Menu Button (Opcional, necesita JS para funcionar) -->
                <div class="-mr-2 flex md:hidden">
                    <button type="button" class="bg-blue-800 inline-flex items-center justify-center p-2 rounded-md text-gray-300 hover:text-white hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-800 focus:ring-white" aria-controls="mobile-menu" aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                        </svg>
                        <svg class="hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu (Necesita JS para funcionar) -->
        <div class="md:hidden hidden" id="mobile-menu"> {{-- Añadido 'hidden' inicial --}}
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="{{ route('libros.index') }}" class="text-gray-200 hover:text-white hover:bg-blue-600 block px-3 py-2 rounded-md text-base font-medium">Libros</a>
                {{-- Autores (Visible solo si NO es cliente logueado) --}}
                @auth
                    @if(Auth::user()->rol != 'cliente')
                        <a href="{{ route('autores.index') }}" class="text-gray-200 hover:text-white hover:bg-blue-600 block px-3 py-2 rounded-md text-base font-medium">Autores</a>
                    @endif
                @endauth
                {{-- Fin Condición Autores --}}


                @guest
                    <a href="{{ route('login') }}" class="text-gray-200 hover:text-white hover:bg-blue-600 block px-3 py-2 rounded-md text-base font-medium">Login</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="text-gray-200 hover:text-white hover:bg-blue-600 block px-3 py-2 rounded-md text-base font-medium">Register</a>
                    @endif
                @else
                    <a href="{{ route('profile.edit') }}" class="text-gray-200 hover:text-white hover:bg-blue-600 block px-3 py-2 rounded-md text-base font-medium">Perfil</a>
                    <form method="POST" action="{{ route('logout') }}" class="block">
                        @csrf
                        <button type="submit" class="w-full text-left text-gray-200 hover:text-white hover:bg-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                            Logout
                        </button>
                    </form>
                @endguest
            </div>
        </div>
    </nav>
    {{-- FIN DE TU BARRA DE NAVEGACIÓN PERSONALIZADA --}}


    <!-- Page Content -->
    <main class="flex-grow container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Session Messages --}}
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
         @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        {{-- Main Content Area - Aquí se inyectará el contenido de login.blade.php --}}
        {{ $slot }}  {{-- <--- CAMBIADO DE @yield('content') A $slot --}}

        {{-- ELIMINADO EL BLOQUE DUPLICADO QUE EMPEZABA CON: <!-- Ejemplo dentro de resources/views/layouts/app.blade.php --> --}}
        {{-- Y QUE CONTENÍA @include('layouts.navigation') Y OTRO {{ $slot }} --}}

    </main> {{-- Cierre del <main> principal --}}


    <!-- Footer -->
    <footer class="bg-gray-600 text-gray-300 mt-auto">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4 text-center text-sm">
            &copy; {{ date('Y') }} SphereWork. Todos los derechos reservados.
        </div>
    </footer>

    <!-- Page Specific Scripts -->
    @stack('scripts')

    {{-- Basic JS for Mobile Menu Toggle (Example) --}}
    <script>
        const btn = document.querySelector('button[aria-controls="mobile-menu"]');
        const menu = document.getElementById('mobile-menu');
        if (btn && menu) { // Check if elements exist before adding listener
            const icons = btn.querySelectorAll('svg');

            // Hide menu initially if JS is enabled
            menu.classList.add('hidden');
            if (icons.length > 1) {
                 icons[1].classList.add('hidden'); // Hide close icon
                 icons[0].classList.remove('hidden'); // Show hamburger icon
            }


            btn.addEventListener('click', () => {
                const expanded = btn.getAttribute('aria-expanded') === 'true' || false;
                btn.setAttribute('aria-expanded', !expanded);
                menu.classList.toggle('hidden');
                if (icons.length > 1) {
                    icons[0].classList.toggle('hidden'); // Toggle hamburger
                    icons[1].classList.toggle('hidden'); // Toggle close icon
                }
            });
        }
    </script>

</body>
</html>
