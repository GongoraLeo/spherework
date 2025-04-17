{{-- filepath: resources/views/admin/editoriales/index.blade.php --}}
{{-- ***** MODIFICADO: Cambiado @extends por <x-app-layout> ***** --}}
    <x-app-layout>

        {{-- Encabezado con Título y Botón Crear --}}
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Lista de Editoriales</h1>
            {{-- Ruta verificada: admin.editoriales.create --}}
            <a href="{{ route('admin.editoriales.create') }}"
               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                {{ __('Crear Nueva Editorial') }}
            </a>
        </div>
    
        {{-- Contenedor tipo tarjeta para la lista --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                {{-- Lista de editoriales --}}
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    {{-- Usa $editoriales (plural) como te gusta --}}
                    @forelse ($editoriales as $editorial)
                        <li class="py-3 flex justify-between items-center">
                            <span class="text-gray-900 dark:text-gray-100">{{ $editorial->nombre }} - <span class="text-sm text-gray-600 dark:text-gray-400">{{ $editorial->pais }}</span></span>
    
                            <div class="flex space-x-2">
                                {{-- Ruta verificada: admin.editoriales.show --}}
                                <a href="{{ route('admin.editoriales.show', $editorial) }}"
                                   title="Ver Detalles"
                                   class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 dark:border-gray-600 shadow-sm text-xs font-medium rounded text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                    Ver
                                </a>
                                {{-- Ruta verificada: admin.editoriales.edit --}}
                                <a href="{{ route('admin.editoriales.edit', $editorial) }}"
                                   title="Editar"
                                   class="inline-flex items-center px-2.5 py-1.5 border border-transparent rounded shadow-sm text-xs font-medium text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 dark:focus:ring-offset-gray-800">
                                    Editar
                                </a>
                                {{-- Ruta verificada: admin.editoriales.destroy --}}
                                <form method="POST" action="{{ route('admin.editoriales.destroy', $editorial) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            title="Eliminar"
                                            class="inline-flex items-center px-2.5 py-1.5 border border-transparent rounded shadow-sm text-xs font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-800"
                                            onclick="return confirm('¿Estás seguro de que quieres eliminar a {{ $editorial->nombre }}?')">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </li>
                    @empty
                        <li class="py-3 text-gray-500 dark:text-gray-400">No hay editoriales registradas.</li>
                    @endforelse
                </ul>
    
                 {{-- Paginación --}}
                 @if ($editoriales instanceof \Illuminate\Pagination\LengthAwarePaginator && $editoriales->hasPages())
                    <div class="mt-4">
                        {{ $editoriales->links() }}
                    </div>
                 @endif
    
            </div>
        </div>
    </x-app-layout> {{-- ***** MODIFICADO: Cierre de <x-app-layout> ***** --}}
    