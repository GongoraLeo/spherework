{{-- filepath: resources/views/pedidos/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Detalles del Pedido') }} #{{ $pedidos->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8"> {{-- Ancho adecuado para detalles --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100"> {{-- Más padding en pantallas medianas --}}

                    {{-- Información General del Pedido --}}
                    <div class="mb-6 border-b border-gray-200 dark:border-gray-700 pb-6">
                        <h3 class="text-lg font-semibold mb-4">Información General</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p><strong class="font-medium text-gray-600 dark:text-gray-400">Número de Pedido:</strong> {{ $pedidos->id }}</p>
                                <p><strong class="font-medium text-gray-600 dark:text-gray-400">Fecha:</strong> {{ $pedidos->fecha_pedido ? $pedidos->fecha_pedido->format('d/m/Y H:i') : 'N/A' }}</p>
                            </div>
                            <div>
                                <p><strong class="font-medium text-gray-600 dark:text-gray-400">Estado:</strong> <span class="font-semibold capitalize">{{ $pedidos->status }}</span></p>
                                <p class="text-lg font-bold mt-1"><strong class="font-medium text-gray-600 dark:text-gray-400">Total:</strong> {{ number_format($pedidos->total ?? 0, 2, ',', '.') }} €</p>
                            </div>
                            {{-- Mostrar información del cliente (si la relación 'cliente' está cargada) --}}
                            @if($pedidos->cliente)
                                <div class="sm:col-span-2 mt-2">
                                     <p><strong class="font-medium text-gray-600 dark:text-gray-400">Cliente:</strong> {{ $pedidos->cliente->name }} ({{ $pedidos->cliente->email }})</p>
                                     {{-- Podrías añadir dirección de envío si la tienes asociada al pedido --}}
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Detalles de los Artículos del Pedido --}}
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">Artículos del Pedido</h3>
                        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-md">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Libro</th>
                                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cantidad</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Precio Unitario</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse ($pedidos->detallespedidos as $detalle)
                                        <tr>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{-- Accedemos al título a través de la relación 'libro' cargada en el controlador --}}
                                                {{ $detalle->libro->titulo ?? 'Libro no disponible' }}
                                                @if($detalle->libro)
                                                    <span class="text-xs text-gray-500 dark:text-gray-400 block">ISBN: {{ $detalle->libro->isbn }}</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">{{ $detalle->cantidad }}</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-right">{{ number_format($detalle->precio, 2, ',', '.') }} €</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-right font-medium">{{ number_format($detalle->cantidad * $detalle->precio, 2, ',', '.') }} €</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                                Este pedido no tiene artículos asociados (puede ser un error).
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                {{-- Pie de tabla opcional para mostrar el total de nuevo --}}
                                <tfoot class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-gray-700 dark:text-gray-200 uppercase">Total Pedido:</td>
                                        <td class="px-4 py-3 text-right text-sm font-bold text-gray-900 dark:text-gray-100">{{ number_format($pedidos->total ?? 0, 2, ',', '.') }} €</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    {{-- Botón para Volver --}}
                    <div class="mt-8 flex justify-start">
                        {{-- Vuelve al panel de perfil del usuario --}}
                        <a href="{{ route('profile.show') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            &laquo; Volver a Mi Panel
                        </a>
                        {{-- Opcional: Si tienes una vista de índice de pedidos para el usuario --}}
                        {{-- @if(Route::has('pedidos.index'))
                            <a href="{{ route('pedidos.index') }}" class="ml-4 inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Ver Todos Mis Pedidos
                            </a>
                        @endif --}}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
