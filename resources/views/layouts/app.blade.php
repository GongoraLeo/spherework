{{-- filepath: resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ***** REVERTIDO: Título estándar de Breeze/componente ***** --}}
    <title>{{ config('app.name', 'SphereWork') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles & Scripts (Using Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Stacks eliminados, generalmente no se usan en el layout base con componentes --}}
    {{-- @stack('styles') --}}

</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen flex flex-col">

    {{-- TU Navigation Bar Personalizada (Azul) - SIN CAMBIOS --}}
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
                    {{-- Enlace Libros Principal --}}
                    <a href="{{ route('libros.index') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('libros.index') ? 'bg-blue-800' : '' }}">Libros</a>

                    {{-- Enlaces solo para usuarios autenticados --}}
                    @auth
                        {{-- Carrito (Oculto para Admin) --}}
                        @if(Auth::user()->rol !== 'administrador')
                            <a href="{{ route('detallespedidos.index') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('detallespedidos.index') ? 'bg-blue-800' : '' }}">
                                Carrito
                            </a>
                        @endif

                        {{-- Enlaces específicos de Rol --}}
                        @if(Auth::user()->rol === 'administrador')
                            {{-- Enlaces solo para Administradores --}}
                            <a href="{{ route('admin.autores.index') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.autores.*') ? 'bg-blue-800' : '' }}">Autores</a>
                            <a href="{{ route('admin.editoriales.index') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.editoriales.*') ? 'bg-blue-800' : '' }}">Editoriales</a>
                            <a href="{{ route('pedidos.index') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('pedidos.*') ? 'bg-blue-800' : '' }}">Pedidos</a>
                            <a href="{{ route('admin.clientes.index') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.clientes.*') ? 'bg-blue-800' : '' }}">Clientes</a>
                            {{-- Enlace "Gestion Libros" sigue eliminado --}}
                        @else
                            {{-- Enlaces para clientes --}}
                            {{-- <a href="{{ route('pedidos.index') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('pedidos.*') ? 'bg-blue-800' : '' }}">Mis Pedidos</a> --}}
                        @endif

                        {{-- Perfil --}}
                        <a href="{{ route('profile.entry') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs(['profile.show', 'admin.dashboard']) ? 'bg-blue-800' : '' }}">Perfil</a>

                        {{-- Logout --}}
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                                Logout ({{ Auth::user()->name }})
                            </button>
                        </form>
                    @endauth

                    {{-- Enlaces solo para invitados --}}
                    @guest
                        <a href="{{ route('login') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('login') ? 'bg-blue-800' : '' }}">Login</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('register') ? 'bg-blue-800' : '' }}">Register</a>
                        @endif
                    @endguest
                </div>

                <!-- Mobile Menu Button -->
                {{-- ... botón menú móvil (sin cambios) ... --}}
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
        {{-- ... menú móvil (sin cambios estructurales, solo enlaces internos) ... --}}
        <div class="md:hidden hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                 {{-- Enlace Libros Principal --}}
                <a href="{{ route('libros.index') }}" class="text-gray-200 hover:text-white hover:bg-blue-600 block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('libros.index') ? 'bg-blue-800' : '' }}">Libros</a>

                {{-- Enlaces solo para usuarios autenticados (Mobile) --}}
                @auth
                    {{-- Carrito (Oculto para Admin - Mobile) --}}
                    @if(Auth::user()->rol !== 'administrador')
                        <a href="{{ route('detallespedidos.index') }}" class="text-gray-200 hover:text-white hover:bg-blue-600 block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('detallespedidos.index') ? 'bg-blue-800' : '' }}">
                            Carrito
                        </a>
                    @endif

                    {{-- Enlaces específicos de Rol (Mobile) --}}
                    @if(Auth::user()->rol === 'administrador')
                         {{-- Enlaces solo para Administradores (Mobile) --}}
                         <a href="{{ route('admin.autores.index') }}" class="text-gray-200 hover:text-white hover:bg-blue-600 block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.autores.*') ? 'bg-blue-800' : '' }}">Autores</a>
                         <a href="{{ route('admin.editoriales.index') }}" class="text-gray-200 hover:text-white hover:bg-blue-600 block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.editoriales.*') ? 'bg-blue-800' : '' }}">Editoriales</a>
                         <a href="{{ route('pedidos.index') }}" class="text-gray-200 hover:text-white hover:bg-blue-600 block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('pedidos.*') ? 'bg-blue-800' : '' }}">Pedidos</a>
                         <a href="{{ route('admin.clientes.index') }}" class="text-gray-200 hover:text-white hover:bg-blue-600 block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.clientes.*') ? 'bg-blue-800' : '' }}">Clientes</a>
                         {{-- Enlace "Gestion Libros" sigue eliminado --}}

                    @else
                        {{-- Enlaces para clientes (Mobile) --}}
                        {{-- <a href="{{ route('pedidos.index') }}" class="text-gray-200 hover:text-white hover:bg-blue-600 block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('pedidos.*') ? 'bg-blue-800' : '' }}">Mis Pedidos</a> --}}
                    @endif

                    {{-- Perfil (Mobile) --}}
                    <a href="{{ route('profile.entry') }}" class="text-gray-200 hover:text-white hover:bg-blue-600 block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs(['profile.show', 'admin.dashboard']) ? 'bg-blue-800' : '' }}">Perfil</a>

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
                    {{-- ... guest links ... --}}
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
        {{-- Session Messages (Se mantienen, son útiles) --}}
        @if (session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">¡Éxito!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
         @if (session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">¡Error!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif
         @if (session('info'))
            <div class="mb-4 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Info:</strong>
                <span class="block sm:inline">{{ session('info') }}</span>
            </div>
        @endif

        {{-- ***** REVERTIDO: Vuelve a usar {{ $slot }} ***** --}}
        {{ $slot }}

    </main> {{-- Cierre del <main> principal --}}

    <!-- Footer -->
    {{-- ... footer (sin cambios) ... --}}
    <footer class="bg-gray-600 text-gray-300 mt-auto">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4 text-center text-sm">
            &copy; {{ date('Y') }} SphereWork. Todos los derechos reservados.
        </div>
    </footer>

    {{-- Stacks eliminados --}}
    {{-- @stack('scripts') --}}

    {{-- Basic JS for Mobile Menu Toggle (Example) - SIN CAMBIOS --}}
    <script>
        // Script para el menú móvil (sin cambios)
        const btn = document.querySelector('button[aria-controls="mobile-menu"]');
        const menu = document.getElementById('mobile-menu');
        if (btn && menu) {
            const icons = btn.querySelectorAll('svg');
            menu.classList.add('hidden'); // Asegura que esté oculto al cargar
            if (icons.length > 1) {
                 icons[1].classList.add('hidden'); // Oculta el icono de cerrar
                 icons[0].classList.remove('hidden'); // Muestra el icono de hamburguesa
            }
            btn.addEventListener('click', () => {
                const expanded = btn.getAttribute('aria-expanded') === 'true' || false;
                btn.setAttribute('aria-expanded', !expanded);
                menu.classList.toggle('hidden');
                if (icons.length > 1) {
                    icons[0].classList.toggle('hidden'); // Alterna visibilidad de iconos
                    icons[1].classList.toggle('hidden');
                }
            });
        }
    </script>

</body>
</html>
