{{-- filepath: resources/views/admin/clientes/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{-- Título: Mostrar nombre del cliente --}}
            {{ __('Detalles del Cliente') }}: {{ $cliente->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Botón Volver a la Lista de Clientes --}}
            <div class="flex justify-start mb-4">
                <a href="{{ route('admin.clientes.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    &laquo; Volver a Clientes
                </a>
            </div>

            {{-- Información Básica del Cliente --}}
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Información del Cliente
                    </h3>
                    {{-- Usar el objeto $cliente pasado desde el controlador --}}
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        ID: {{ $cliente->id }}
                    </p>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Nombre: {{ $cliente->name }}
                    </p>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Email: {{ $cliente->email }}
                    </p>
                     <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Registrado: {{ $cliente->created_at->format('d/m/Y H:i') }}
                    </p>
                    {{-- Aquí podrías añadir más info si la tienes: dirección, teléfono, etc. --}}
                    {{-- O botones de acción para el admin: Editar Cliente, Banear, etc. --}}
                </div>
            </div>

            {{-- Pedidos Recientes del Cliente --}}
            {{-- Esta sección es idéntica a la de profile.show, ya usa la variable $pedidos --}}
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-full">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Pedidos Recientes del Cliente
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
                                {{-- La variable $pedidos viene del controlador --}}
                                @forelse ($pedidos as $pedido)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">#{{ $pedido->id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $pedido->fecha_pedido ? $pedido->fecha_pedido->format('d/m/Y H:i') : 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ ucfirst($pedido->status) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-right">{{ number_format($pedido->total ?? 0, 2, ',', '.') }} €</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                            {{-- Enlace a la vista de detalles del pedido (pedidos.show) --}}
                                            @if(Route::has('pedidos.show'))
                                                <a href="{{ route('pedidos.show', $pedido) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">Ver Detalles</a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                            Este cliente no ha realizado ningún pedido todavía.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Comentarios Recientes del Cliente --}}
            {{-- Esta sección es casi idéntica, pero NO debe tener botones de editar/eliminar para el admin (a menos que quieras esa funcionalidad explícita) --}}
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Comentarios Recientes del Cliente
                    </h3>
                    <div class="space-y-6">
                        {{-- La variable $comentarios viene del controlador --}}
                        @forelse ($comentarios as $comentario)
                            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 last:border-b-0">
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
                                {{-- NO incluir botones de editar/eliminar aquí (a menos que sea una función admin específica) --}}
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Este cliente no ha dejado ningún comentario todavía.
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Sección Valoraciones (opcional, si quieres mostrarla separada) --}}
            {{-- Idéntica a profile.show, usa la variable $comentarios --}}
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Valoraciones Recientes del Cliente
                    </h3>
                    <div class="space-y-3">
                        @php $valoracionesMostradas = 0; @endphp
                        @foreach ($comentarios as $comentario) {{-- Reutiliza $comentarios --}}
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
                        @if($valoracionesMostradas === 0)
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Este cliente no ha realizado ninguna valoración todavía.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
