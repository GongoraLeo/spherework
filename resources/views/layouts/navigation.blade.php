{{-- filepath: resources/views/layouts/navigation.blade.php --}}
<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    {{-- Enlace del logo: apunta a libros.index si es la página principal, o dashboard si prefieres --}}
                    <a href="{{ route('libros.index') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    {{-- Enlace principal: Catálogo de Libros --}}
                    <x-nav-link :href="route('libros.index')" :active="request()->routeIs('libros.index')">
                        {{ __('Catálogo') }}
                    </x-nav-link>

                    {{-- Enlace al Carrito (si existe la ruta) --}}
                    @if(Route::has('carrito.index'))
                        <x-nav-link :href="route('carrito.index')" :active="request()->routeIs('carrito.index')">
                            {{ __('Carrito') }}
                            {{-- Opcional: Mostrar contador de items en el carrito --}}
                            {{-- @inject('cartService', 'App\Services\CartService') --}}
                            {{-- @if($cartService->count() > 0) --}}
                            {{--     <span class="ml-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full">{{ $cartService->count() }}</span> --}}
                            {{-- @endif --}}
                        </x-nav-link>
                    @endif

                    {{-- Enlaces solo para Administradores --}}
                    @auth
                        @if(Auth::user()->rol === 'administrador')
                            {{-- Ejemplo: Enlace a gestión de usuarios (si tienes esa sección) --}}
                            {{-- <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')"> --}}
                            {{--     {{ __('Usuarios') }} --}}
                            {{-- </x-nav-link> --}}
                            {{-- Ejemplo: Enlace a gestión de pedidos (si tienes esa sección) --}}
                            @if(Route::has('admin.pedidos.index'))
                                <x-nav-link :href="route('admin.pedidos.index')" :active="request()->routeIs('admin.pedidos.*')">
                                    {{ __('Gestionar Pedidos') }}
                                </x-nav-link>
                            @endif
                        @endif
                    @endauth

                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth {{-- Mostrar solo si el usuario está autenticado --}}
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            {{-- ***** MODIFICADO AQUÍ ***** --}}
                            <x-dropdown-link :href="route('profile.show')">
                                {{ __('Mi Panel') }} {{-- Texto cambiado --}}
                            </x-dropdown-link>
                            {{-- ***** FIN MODIFICACIÓN ***** --}}

                            {{-- Enlace a Editar Perfil (opcional, ya está dentro de Mi Panel) --}}
                            {{-- <x-dropdown-link :href="route('profile.edit')"> --}}
                            {{--    {{ __('Editar Perfil') }} --}}
                            {{-- </x-dropdown-link> --}}

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Cerrar Sesión') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else {{-- Mostrar enlaces de Login/Register si no está autenticado --}}
                    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                        <x-nav-link :href="route('login')">
                            {{ __('Iniciar Sesión') }}
                        </x-nav-link>
                        @if (Route::has('register'))
                            <x-nav-link :href="route('register')">
                                {{ __('Registrarse') }}
                            </x-nav-link>
                        @endif
                    </div>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            {{-- Enlace principal responsive --}}
            <x-responsive-nav-link :href="route('libros.index')" :active="request()->routeIs('libros.index')">
                {{ __('Catálogo') }}
            </x-responsive-nav-link>

            {{-- Enlace al Carrito responsive (si existe) --}}
            @if(Route::has('carrito.index'))
                <x-responsive-nav-link :href="route('carrito.index')" :active="request()->routeIs('carrito.index')">
                    {{ __('Carrito') }}
                </x-responsive-nav-link>
            @endif

             {{-- Enlaces solo para Administradores responsive --}}
             @auth
                @if(Auth::user()->rol === 'administrador')
                    @if(Route::has('admin.pedidos.index'))
                        <x-responsive-nav-link :href="route('admin.pedidos.index')" :active="request()->routeIs('admin.pedidos.*')">
                            {{ __('Gestionar Pedidos') }}
                        </x-responsive-nav-link>
                    @endif
                @endif
             @endauth
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            @auth {{-- Mostrar solo si el usuario está autenticado --}}
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    {{-- ***** MODIFICADO AQUÍ ***** --}}
                    <x-responsive-nav-link :href="route('profile.show')">
                        {{ __('Mi Panel') }} {{-- Texto cambiado --}}
                    </x-responsive-nav-link>
                    {{-- ***** FIN MODIFICACIÓN ***** --}}

                    {{-- Enlace a Editar Perfil responsive (opcional) --}}
                    {{-- <x-responsive-nav-link :href="route('profile.edit')"> --}}
                    {{--    {{ __('Editar Perfil') }} --}}
                    {{-- </x-responsive-nav-link> --}}

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('Cerrar Sesión') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            @else {{-- Mostrar Login/Register en responsive si no está autenticado --}}
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('login')">
                        {{ __('Iniciar Sesión') }}
                    </x-responsive-nav-link>
                    @if (Route::has('register'))
                        <x-responsive-nav-link :href="route('register')">
                            {{ __('Registrarse') }}
                        </x-responsive-nav-link>
                    @endif
                </div>
            @endauth
        </div>
    </div>
</nav>
