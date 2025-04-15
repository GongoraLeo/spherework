{{-- filepath: resources/views/pedidos/success.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Pedido Realizado con Éxito') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Mensaje de éxito principal --}}
                    @if (session('success'))
                        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">¡Gracias!</strong>
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <h3 class="text-lg font-semibold mb-4">Resumen de tu Pedido</h3>

                    <div class="mb-4 border-b pb-4 dark:border-gray-700">
                        <p><strong class="font-medium">Número de Pedido:</strong> {{ $pedido->id }}</p>
                        <p><strong class="font-medium">Fecha del Pedido:</strong> {{ $pedido->fecha_pedido->format('d/m/Y H:i') }}</p>
                        <p><strong class="font-medium">Estado:</strong> <span class="capitalize">{{ $pedido->status }}</span></p> {{-- capitalize para 'Completado' --}}
                        <p class="text-xl font-bold mt-2"><strong class="font-medium">Total Pagado:</strong> {{ number_format($pedido->total, 2, ',', '.') }} €</p>
                    </div>

                    {{-- Opcional: Mostrar los items del pedido --}}
                    <h4 class="text-md font-semibold mb-2">Artículos:</h4>
                    <ul class="list-disc list-inside mb-6 space-y-1 text-sm text-gray-600 dark:text-gray-400">
                        @foreach ($pedido->detallespedido as $detalle)
                            <li>
                                {{ $detalle->cantidad }} x {{ $detalle->libro->titulo ?? 'N/A' }}
                                ({{ number_format($detalle->precio, 2, ',', '.') }} € c/u)
                            </li>
                        @endforeach
                    </ul>

                    {{-- Enlaces de navegación --}}
                    <div class="flex justify-start space-x-4">
                        <a href="{{ route('libros.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Seguir Comprando
                        </a>
                        {{-- Puedes añadir un enlace al historial de pedidos si lo tienes --}}
                        {{-- <a href="{{ route('pedidos.history') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Ver Mis Pedidos
                        </a> --}}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
