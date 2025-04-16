{{-- filepath: resources/views/profile/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Mi Panel de Usuario') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Mensaje de Bienvenida e Información Básica --}}
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        ¡Hola, {{ Auth::user()->name }}!
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Bienvenido/a a tu panel personal. Aquí puedes ver un resumen de tu actividad en SphereWork y gestionar tu cuenta.
                    </p>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Email: {{ Auth::user()->email }}
                    </p>
                     {{-- <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Miembro desde: {{ Auth::user()->created_at->format('d/m/Y') }}
                    </p> --}}
                </div>
            </div>

            {{-- Acciones de Cuenta --}}
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Gestionar mi Cuenta
                    </h3>
                    <div class="space-y-2">
                        {{-- Enlace a la página de edición de perfil (que ahora está en /profile/edit) --}}
                        <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Editar Información del Perfil y Contraseña
                        </a>
                    </div>
                </div>
            </div>

            {{-- Mis Pedidos Recientes --}}
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-full">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Mis Pedidos Recientes
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID Pedido</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($pedidos as $pedido)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">#{{ $pedido->id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $pedido->fecha_pedido ? $pedido->fecha_pedido->format('d/m/Y H:i') : 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ ucfirst($pedido->status) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-right">{{ number_format($pedido->total ?? 0, 2, ',', '.') }} €</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                            {{-- Asumiendo ruta 'pedidos.show' para ver detalles (puede ser la misma que admin o una específica de usuario) --}}
                                            @if(Route::has('pedidos.show'))
                                                <a href="{{ route('pedidos.show', $pedido) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">Ver Detalles</a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                            No has realizado ningún pedido todavía.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                     {{-- Enlace para ver todos los pedidos (si tienes esa ruta) --}}
                     {{-- @if ($pedidos->count() > 0)
                        <div class="mt-4">
                            <a href="{{ route('pedidos.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                Ver todos mis pedidos
                            </a>
                        </div>
                    @endif --}}
                </div>
            </div>

            {{-- Mis Comentarios Recientes --}}
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl"> {{-- Ajustado a max-w-xl para consistencia --}}
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Mis Comentarios Recientes
                    </h3>
                    <div class="space-y-6"> {{-- Aumentado el espacio entre comentarios --}}
                        @forelse ($comentarios as $comentario)
                            {{-- Contenedor para cada comentario y sus acciones --}}
                            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 last:border-b-0"> {{-- last:border-b-0 quita borde al último --}}
                                <div class="border-l-4 border-gray-300 dark:border-gray-600 pl-4">
                                    <p class="text-sm text-gray-800 dark:text-gray-200 italic">"{{ $comentario->comentario }}"</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        En el libro:
                                        @if($comentario->libro && Route::has('libros.show'))
                                            <a href="{{ route('libros.show', $comentario->libro) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $comentario->libro->titulo }}</a>
                                        @else
                                            <span class="italic">Libro no disponible</span>
                                        @endif
                                        - {{ $comentario->created_at->diffForHumans() }}
                                        @if($comentario->puntuacion)
                                            <span class="ml-2 text-yellow-500">({{ $comentario->puntuacion }} ★)</span>
                                        @endif
                                    </p>
                                </div>
                                {{-- ***** INICIO: Botones de acción para editar/eliminar ***** --}}
                                <div class="mt-2 pl-4 flex items-center space-x-3">
                                    {{-- Botón Editar --}}
                                    @if(Route::has('comentarios.edit'))
                                        <a href="{{ route('comentarios.edit', $comentario) }}" class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium">
                                            Editar
                                        </a>
                                    @endif

                                    {{-- Botón Eliminar (dentro de un formulario) --}}
                                    @if(Route::has('comentarios.destroy'))
                                        <form method="POST" action="{{ route('comentarios.destroy', $comentario) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-xs text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 font-medium"
                                                    onclick="return confirm('¿Estás seguro de que quieres eliminar este comentario?')">
                                                Eliminar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                {{-- ***** FIN: Botones de acción para editar/eliminar ***** --}}
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                No has dejado ningún comentario todavía.
                            </p>
                        @endforelse
                    </div>
                     {{-- Enlace para ver todos los comentarios (si tienes esa ruta) --}}
                     {{-- <div class="mt-4">
                        <a href="#" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                            Ver todos mis comentarios
                        </a>
                    </div> --}}
                </div>
            </div>

            {{-- Mis Valoraciones Recientes (Basado en Comentarios con Puntuación) --}}
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Mis Valoraciones Recientes
                    </h3>
                    <div class="space-y-3">
                        @php $valoracionesMostradas = 0; @endphp
                        @foreach ($comentarios as $comentario)
                            @if($comentario->puntuacion)
                                @php $valoracionesMostradas++; @endphp
                                <div class="flex items-center justify-between">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Libro:
                                        @if($comentario->libro && Route::has('libros.show'))
                                            <a href="{{ route('libros.show', $comentario->libro) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $comentario->libro->titulo }}</a>
                                        @else
                                            <span class="italic">Libro no disponible</span>
                                        @endif
                                    </p>
                                    <p class="text-sm font-semibold text-yellow-500 flex items-center">
                                        {{-- Muestra estrellas llenas y vacías --}}
                                        @for ($i = 1; $i <= 5; $i++)
                                            @if ($i <= $comentario->puntuacion)
                                                <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                            @else
                                                <svg class="w-4 h-4 fill-current text-gray-300 dark:text-gray-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                            @endif
                                        @endfor
                                        <span class="ml-1">({{ $comentario->puntuacion }})</span>
                                    </p>
                                </div>
                            @endif
                        @endforeach

                        {{-- Mensaje si no hay valoraciones (comentarios con puntuación) --}}
                        @if($valoracionesMostradas === 0)
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                No has realizado ninguna valoración todavía.
                            </p>
                        @endif
                    </div>
                    {{-- Enlace para ver todas las valoraciones (si tienes esa funcionalidad) --}}
                    {{-- <div class="mt-4">
                        <a href="#" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                            Ver todas mis valoraciones
                        </a>
                    </div> --}}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
