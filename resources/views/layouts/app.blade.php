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
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen flex flex-col"> {{-- Añadido dark mode a body --}}

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

                <!-- Navigation Links (Desktop) -->
                <div class="hidden md:flex items-center space-x-4">
                    <a href="{{ route('libros.index') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('libros.index') ? 'bg-blue-800' : '' }}">Libros</a>

                    {{-- Enlaces solo para usuarios autenticados --}}
                    @auth
                        {{-- Carrito (Visible para todos los logueados) --}}
                        {{-- ***** INICIO: Enlace Carrito (Desktop) ***** --}}
                        <a href="{{ route('detallespedidos.index') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('detallespedidos.index') ? 'bg-blue-800' : '' }}">
                            Carrito
                            {{-- Opcional: Añadir contador de items aquí si lo implementas --}}
                            {{-- @inject('cartService', 'App\Services\CartService') --}}
                            {{-- @if($cartService->count() > 0) --}}
                            {{-- <span class="ml-1 bg-red-500 text-white text-xs font-bold rounded-full px-1.5 py-0.5">{{ $cartService->count() }}</span> --}}
                            {{-- @endif --}}
                        </a>
                        {{-- ***** FIN: Enlace Carrito (Desktop) ***** --}}


                        {{-- Autores (Visible solo si NO es cliente logueado) --}}
                        @if(Auth::user()->rol != 'cliente')
                            <a href="{{ route('autores.index') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('autores.index') ? 'bg-blue-800' : '' }}">Autores</a>
                        @endif
                        {{-- Fin Condición Autores --}}

                        {{-- Perfil --}}
                        <a href="{{ route('profile.edit') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('profile.edit') ? 'bg-blue-800' : '' }}">Perfil</a>

                        {{-- Logout --}}
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                                Logout ({{ Auth::user()->name }})
                            </button>
                        </form>
                    @endauth

                    {{-- Enlaces solo para invitados (no logueados) --}}
                    @guest
                        <a href="{{ route('login') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('login') ? 'bg-blue-800' : '' }}">Login</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('register') ? 'bg-blue-800' : '' }}">Register</a>
                        @endif
                    @endguest
                </div>

                <!-- Mobile Menu Button -->
                <div class="-mr-2 flex md:hidden">
                    <button type="button" class="bg-blue-800 inline-flex items-center justify-center p-2 rounded-md text-gray-300 hover:text-white hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-800 focus:ring-white" aria-controls="mobile-menu" aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        {{-- Hamburger Icon --}}
                        <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                        </svg>
                        {{-- Close Icon --}}
                        <svg class="hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div class="md:hidden hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="{{ route('libros.index') }}" class="text-gray-200 hover:text-white hover:bg-blue-600 block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('libros.index') ? 'bg-blue-800' : '' }}">Libros</a>

                {{-- Enlaces solo para usuarios autenticados (Mobile) --}}
                @auth
                    {{-- Carrito (Mobile) --}}
                    {{-- ***** INICIO: Enlace Carrito (Mobile) ***** --}}
                    <a href="{{ route('detallespedidos.index') }}" class="text-gray-200 hover:text-white hover:bg-blue-600 block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('detallespedidos.index') ? 'bg-blue-800' : '' }}">
                        Carrito
                        {{-- Opcional: Añadir contador de items aquí --}}
                    </a>
                    {{-- ***** FIN: Enlace Carrito (Mobile) ***** --}}

                    {{-- Autores (Mobile) --}}
                    @if(Auth::user()->rol != 'cliente')
                        <a href="{{ route('autores.index') }}" class="text-gray-200 hover:text-white hover:bg-blue-600 block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('autores.index') ? 'bg-blue-800' : '' }}">Autores</a>
                    @endif

                    {{-- Perfil (Mobile) --}}
                    <a href="{{ route('profile.edit') }}" class="text-gray-200 hover:text-white hover:bg-blue-600 block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('profile.edit') ? 'bg-blue-800' : '' }}">Perfil</a>

                    {{-- Logout (Mobile) --}}
                    <form method="POST" action="{{ route('logout') }}" class="block">
                        @csrf
                        <button type="submit" class="w-full text-left text-gray-200 hover:text-white hover:bg-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                            Logout
                        </button>
                    </form>
                @endauth

                {{-- Enlaces solo para invitados (Mobile) --}}
                @guest
                    <a href="{{ route('login') }}" class="text-gray-200 hover:text-white hover:bg-blue-600 block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('login') ? 'bg-blue-800' : '' }}">Login</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="text-gray-200 hover:text-white hover:bg-blue-600 block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('register') ? 'bg-blue-800' : '' }}">Register</a>
                    @endif
                @endguest
            </div>
        </div>
    </nav>
    {{-- FIN DE TU BARRA DE NAVEGACIÓN PERSONALIZADA --}}


    <!-- Page Content -->
    <main class="flex-grow container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Session Messages (Movidos aquí desde el layout original para evitar duplicados si $slot los incluye) --}}
        {{-- Si tus vistas individuales (como libros.index) ya muestran mensajes, puedes eliminar estos --}}
        {{-- @if (session('success'))
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
        @endif --}}

        {{-- Main Content Area --}}
        {{ $slot }}

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
        // Script para el menú móvil (sin cambios)
        const btn = document.querySelector('button[aria-controls="mobile-menu"]');
        const menu = document.getElementById('mobile-menu');
        if (btn && menu) {
            const icons = btn.querySelectorAll('svg');
            menu.classList.add('hidden');
            if (icons.length > 1) {
                 icons[1].classList.add('hidden');
                 icons[0].classList.remove('hidden');
            }
            btn.addEventListener('click', () => {
                const expanded = btn.getAttribute('aria-expanded') === 'true' || false;
                btn.setAttribute('aria-expanded', !expanded);
                menu.classList.toggle('hidden');
                if (icons.length > 1) {
                    icons[0].classList.toggle('hidden');
                    icons[1].classList.toggle('hidden');
                }
            });
        }
    </script>

</body>
</html>
