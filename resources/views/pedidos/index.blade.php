{{-- filepath: resources/views/pedidos/index.blade.php --}}
<x-app-layout>

    {{-- Encabezado --}}
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            {{-- Título dinámico según el rol --}}
            @if(Auth::user()->rol === 'administrador')
                Gestión de Todos los Pedidos
            @else
                Mis Pedidos
            @endif
        </h1>
        {{-- No hay botón Crear --}}
    </div>

    {{-- Contenedor de la tabla --}}
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">

            {{-- Tabla responsiva --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                ID Pedido
                            </th>
                            {{-- Columna Cliente (Solo para Admin) --}}
                            @if(Auth::user()->rol === 'administrador')
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Cliente
                                </th>
                            @endif
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Fecha
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Total
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Estado
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Acciones</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($pedidos as $pedido)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    #{{ $pedido->id }}
                                </td>
                                {{-- Columna Cliente (Solo para Admin) --}}
                                @if(Auth::user()->rol === 'administrador')
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $pedido->user->name ?? 'N/A' }}
                                    </td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{-- Asegurarse que fecha_pedido no sea null --}}
                                    {{ $pedido->fecha_pedido ? $pedido->fecha_pedido->format('d/m/Y H:i') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ number_format($pedido->total ?? 0, 2, ',', '.') }} €
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @switch($pedido->status)
                                            @case(App\Models\Pedidos::STATUS_PENDIENTE) bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100 @break
                                            @case(App\Models\Pedidos::STATUS_COMPLETADO)
                                            @case(App\Models\Pedidos::STATUS_ENTREGADO) bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100 @break
                                            @case(App\Models\Pedidos::STATUS_ENVIADO) bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-100 @break
                                            @case(App\Models\Pedidos::STATUS_CANCELADO) bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100 @break
                                            @default bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100 @break
                                        @endswitch
                                    ">
                                        {{ ucfirst($pedido->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    {{-- Botón Ver Detalles (Visible para ambos roles) --}}
                                    <a href="{{ route('pedidos.show', $pedido) }}"
                                       title="Ver Detalles del Pedido"
                                       class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 dark:border-gray-600 shadow-sm text-xs font-medium rounded text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                        Ver
                                    </a>

                                    {{-- Acciones solo para Administrador --}}
                                    @if(Auth::user()->rol === 'administrador')
                                        {{-- ***** BOTÓN EDITAR ELIMINADO ***** --}}
                                        {{-- <a href="{{ route('pedidos.edit', $pedido) }}"
                                           title="Editar Estado"
                                           class="inline-flex items-center px-2.5 py-1.5 border border-transparent rounded shadow-sm text-xs font-medium text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 dark:focus:ring-offset-gray-800">
                                            Editar
                                        </a> --}}

                                        {{-- ***** BOTÓN ELIMINAR (Cancelar) AÑADIDO/DESCOMENTADO ***** --}}
                                        {{-- Solo mostrar si el pedido NO está ya cancelado --}}
                                        @if($pedido->status !== App\Models\Pedidos::STATUS_CANCELADO)
                                            <form action="{{ route('pedidos.destroy', $pedido) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        title="Eliminar/Cancelar Pedido"
                                                        class="inline-flex items-center px-2.5 py-1.5 border border-transparent rounded shadow-sm text-xs font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-800"
                                                        onclick="return confirm('¿Estás seguro de que quieres eliminar/cancelar este pedido (ID: {{ $pedido->id }})? Esta acción no se puede deshacer.')">
                                                    Eliminar
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ Auth::user()->rol === 'administrador' ? '6' : '5' }}"
                                    class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                    @if(Auth::user()->rol === 'administrador')
                                        No hay pedidos registrados en el sistema.
                                    @else
                                        Aún no has realizado ningún pedido.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
             @if ($pedidos instanceof \Illuminate\Pagination\LengthAwarePaginator && $pedidos->hasPages())
                <div class="mt-4">
                    {{ $pedidos->links() }}
                </div>
             @endif

        </div>
    </div>
</x-app-layout>
