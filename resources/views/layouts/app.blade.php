{{-- filepath: resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"> {{-- Important for forms/AJAX --}}

    <title>@yield('title', 'SphereWork')</title> {{-- Dynamic Title --}}

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles & Scripts (Using Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Additional Page Specific Styles -->
    @stack('styles')

</head>
<body class="font-sans antialiased bg-gray-100 text-gray-800 min-h-screen flex flex-col">

    <!-- Navigation Bar -->
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
                    <a href="{{ route('autores.index') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Autores</a>
                    {{-- Add other common links like genres, etc. --}}

                    {{-- Authentication Links --}}
                    @guest
                        <a href="{{ route('login') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Login</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Register</a>
                        @endif
                    @else
                        {{-- Logged in user links --}}
                        <a href="{{ route('profile.show') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Perfil</a>
                        {{-- Add links to user's bookshelf, comments, etc. --}}

                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                                Logout ({{ Auth::user()->name }})
                            </button>
                        </form>
                    @endguest
                </div>

                <!-- Mobile Menu Button (Optional) -->
                <div class="-mr-2 flex md:hidden">
                    <button type="button" class="bg-blue-800 inline-flex items-center justify-center p-2 rounded-md text-gray-300 hover:text-white hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-800 focus:ring-white" aria-controls="mobile-menu" aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <!-- Icon when menu is closed. -->
                        <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                        </svg>
                        <!-- Icon when menu is open. -->
                        <svg class="hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu, show/hide based on menu state. (Needs JS) -->
        <div class="md:hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="{{ route('libros.index') }}" class="text-gray-200 hover:text-white hover:bg-blue-600 block px-3 py-2 rounded-md text-base font-medium">Libros</a>
                <a href="{{ route('autores.index') }}" class="text-gray-200 hover:text-white hover:bg-blue-600 block px-3 py-2 rounded-md text-base font-medium">Autores</a>
                 @guest
                    <a href="{{ route('login') }}" class="text-gray-200 hover:text-white hover:bg-blue-600 block px-3 py-2 rounded-md text-base font-medium">Login</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="text-gray-200 hover:text-white hover:bg-blue-600 block px-3 py-2 rounded-md text-base font-medium">Register</a>
                    @endif
                @else
                    <a href="{{ route('profile.show') }}" class="text-gray-200 hover:text-white hover:bg-blue-600 block px-3 py-2 rounded-md text-base font-medium">Perfil</a>
                    {{-- Add mobile versions of other auth links --}}
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

    <!-- Page Content -->
    <main class="flex-grow container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Session Messages (Optional but Recommended) --}}
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

        {{-- Main Content Area --}}
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-600 text-gray-300 mt-auto">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4 text-center text-sm">
            &copy; {{ date('Y') }} SphereWork. Todos los derechos reservados.
            {{-- Add other footer links if needed --}}
        </div>
    </footer>

    <!-- Page Specific Scripts -->
    @stack('scripts')

    {{-- Basic JS for Mobile Menu Toggle (Example) --}}
    <script>
        const btn = document.querySelector('button[aria-controls="mobile-menu"]');
        const menu = document.getElementById('mobile-menu');
        const icons = btn.querySelectorAll('svg');

        // Hide menu initially if JS is enabled
        menu.classList.add('hidden');
        icons[1].classList.add('hidden'); // Hide close icon
        icons[0].classList.remove('hidden'); // Show hamburger icon

        btn.addEventListener('click', () => {
            const expanded = btn.getAttribute('aria-expanded') === 'true' || false;
            btn.setAttribute('aria-expanded', !expanded);
            menu.classList.toggle('hidden');
            icons[0].classList.toggle('hidden'); // Toggle hamburger
            icons[1].classList.toggle('hidden'); // Toggle close icon
        });
    </script>

</body>
</html>
