{{-- filepath: resources/views/admin/autores/index.blade.php --}}
{{-- ***** REVERTIDO: Vuelve a usar <x-app-layout> ***** --}}
    <x-app-layout>

        {{-- Encabezado con Título y Botón Crear --}}
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Lista de Autores</h1>
            {{-- Ruta verificada: admin.autores.create --}}
            <a href="{{ route('admin.autores.create') }}"
               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                {{ __('Crear Nuevo Autor') }}
            </a>
        </div>
    
        {{-- Contenedor tipo tarjeta para la lista --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                {{-- Lista de autores --}}
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($autores as $autor)
                        <li class="py-3 flex justify-between items-center">
                            <span class="text-gray-900 dark:text-gray-100">{{ $autor->nombre }} - <span class="text-sm text-gray-600 dark:text-gray-400">{{ $autor->pais }}</span></span>
    
                            <div class="flex space-x-2">
                                {{-- Ruta verificada: admin.autores.show --}}
                                <a href="{{ route('admin.autores.show', $autor) }}"
                                   title="Ver Detalles"
                                   class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 dark:border-gray-600 shadow-sm text-xs font-medium rounded text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                    Ver
                                </a>
    
                                {{-- Ruta verificada: admin.autores.edit --}}
                                <a href="{{ route('admin.autores.edit', $autor) }}"
                                   title="Editar"
                                   class="inline-flex items-center px-2.5 py-1.5 border border-transparent rounded shadow-sm text-xs font-medium text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 dark:focus:ring-offset-gray-800">
                                    Editar
                                </a>
    
                                {{-- Ruta verificada: admin.autores.destroy --}}
                                <form method="POST" action="{{ route('admin.autores.destroy', $autor) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            title="Eliminar"
                                            class="inline-flex items-center px-2.5 py-1.5 border border-transparent rounded shadow-sm text-xs font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-800"
                                            onclick="return confirm('¿Estás seguro de que quieres eliminar a {{ $autor->nombre }}?')">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </li>
                    @empty
                        <li class="py-3 text-gray-500 dark:text-gray-400">No hay autores registrados.</li>
                    @endforelse
                </ul>
    
                 {{-- Paginación --}}
                 @if ($autores instanceof \Illuminate\Pagination\LengthAwarePaginator && $autores->hasPages())
                    <div class="mt-4">
                        {{ $autores->links() }}
                    </div>
                 @endif
    
            </div>
        </div>
    
    </x-app-layout> {{-- ***** REVERTIDO: Cierre de <x-app-layout> ***** --}}
    