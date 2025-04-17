{{-- filepath: resources/views/admin/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Panel de Administración') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Sección de Estadísticas Generales (Ejemplo) --}}
            <div class="md:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Estadísticas Rápidas</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Pedidos</p>
                            <p class="text-2xl font-bold">{{ $totalPedidos ?? 0 }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Clientes Registrados</p>
                            <p class="text-2xl font-bold">{{ $totalClientes ?? 0 }}</p>
                        </div>
                        {{-- Añade más estadísticas aquí --}}
                    </div>
                </div>
            </div>

            {{-- Sección Libros Más Vendidos --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Top Libros Vendidos</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Libro</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Vendidos</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($librosMasVendidos as $libro)
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">{{ $libro->titulo }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-center">{{ $libro->total_vendido }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-center">
                                            {{-- Enlace a la vista pública del libro --}}
                                            <a href="{{ route('libros.show', $libro->id) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">Ver</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-2 text-center text-sm text-gray-500 dark:text-gray-400">No hay datos de ventas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- Aquí podrías añadir un enlace a una página con todos los libros/ventas --}}
                </div>
            </div>

            {{-- Sección Clientes Recientes --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Clientes Recientes</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nombre</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Email</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($clientesRecientes as $cliente)
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">{{ $cliente->name }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm">{{ $cliente->email }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-center">
                                            {{-- ***** CORREGIDO AQUÍ: Usa admin.clientes.show ***** --}}
                                             @if(Route::has('admin.clientes.show'))
                                                <a href="{{ route('admin.clientes.show', $cliente->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Ver Perfil</a>
                                             @else
                                                <span class="text-gray-400 text-xs">Ruta no def.</span>
                                             @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-2 text-center text-sm text-gray-500 dark:text-gray-400">No hay clientes registrados recientemente.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                     {{-- Enlace a la gestión completa de clientes --}}
                     <div class="mt-4">
                         {{-- ***** CORREGIDO AQUÍ: Usa admin.clientes.index ***** --}}
                         <a href="{{ route('admin.clientes.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                             Gestionar todos los clientes &rarr;
                         </a>
                     </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
