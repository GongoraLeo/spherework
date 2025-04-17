{{-- filepath: resources/views/autores/show.blade.php --}}
<x-app-layout>
    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Título con el nombre del autor (lo dejamos alineado a la izquierda por defecto) --}}
                    <h1 class="text-2xl font-semibold mb-4">Detalles del Autor</h1>
                    <h2 class="text-xl font-medium text-gray-800 dark:text-gray-200 mb-6">{{ $autores->nombre }}</h2>

                    {{-- Detalles del Autor - Añadimos text-center aquí --}}
                    <div class="space-y-4 mb-6 text-center">
                        <div>
                            {{-- La etiqueta span también se centrará --}}
                            <span class="font-semibold text-gray-600 dark:text-gray-400">Nombre:</span>
                            {{-- El párrafo con el valor también se centrará --}}
                            <p class="text-gray-900 dark:text-gray-100">{{ $autores->nombre }}</p>
                        </div>
                        <div>
                            <span class="font-semibold text-gray-600 dark:text-gray-400">País:</span>
                            <p class="text-gray-900 dark:text-gray-100">{{ $autores->pais }}</p>
                        </div>
                        {{-- Cualquier otro detalle añadido aquí también estará centrado --}}
                    </div>

                    {{-- Botones de Acción y Volver (los dejamos alineados como estaban) --}}
                    <div class="flex items-center justify-between mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                        {{-- Botón Volver --}}
                        <a href="{{ route('admin.autores.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            {{ __('Volver a la Lista') }}
                        </a>

                        {{-- Grupo de botones Editar y Eliminar --}}
                        <div class="flex space-x-2">
                            {{-- Botón Editar --}}
                            <a href="{{ route('admin.autores.edit', $autores) }}"
                               title="Editar"
                               {{-- Reemplaza '...' con las clases de estilo que tenías --}}
                               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-xs font-medium text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 dark:focus:ring-offset-gray-800">
                                Editar
                            </a>

                            {{-- Botón Eliminar --}}
                            <form method="POST" action="{{ route('admin.autores.destroy', $autores) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        title="Eliminar"
                                        {{-- Reemplaza '...' con las clases de estilo que tenías --}}
                                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-xs font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-800"
                                        onclick="return confirm('¿Estás seguro de que quieres eliminar a {{ $autores->nombre }}?')">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </div>

                </div> {{-- Cierre de div.p-6 --}}
            </div> {{-- Cierre de div.bg-white --}}
        </div> {{-- Cierre de div.max-w-xl --}}
    </div> {{-- Cierre de div.py-12 --}}
</x-app-layout>
