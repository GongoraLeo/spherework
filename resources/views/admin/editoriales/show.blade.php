{{-- filepath: resources/views/admin/editoriales/show.blade.php --}}
{{-- ***** MODIFICADO: Cambiado @extends por <x-app-layout> ***** --}}
    <x-app-layout>

        {{-- Slot para el header (opcional) --}}
        {{-- <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                {{ __('Detalle Editorial') }}
            </h2>
        </x-slot> --}}
    
        <div class="py-12">
            <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
    
                        <h1 class="text-2xl font-semibold mb-4">Detalles de la Editorial</h1>
                        {{-- Usa la variable $editoriales (plural) --}}
                        <h2 class="text-xl font-medium text-gray-800 dark:text-gray-200 mb-6">{{ $editoriales->nombre }}</h2>
    
                        <div class="space-y-4 mb-6">
                            <div>
                                <span class="font-semibold text-gray-600 dark:text-gray-400">Nombre:</span>
                                <p class="text-gray-900 dark:text-gray-100">{{ $editoriales->nombre }}</p>
                            </div>
                            <div>
                                <span class="font-semibold text-gray-600 dark:text-gray-400">País:</span>
                                <p class="text-gray-900 dark:text-gray-100">{{ $editoriales->pais }}</p>
                            </div>
                            {{-- Puedes añadir más detalles si los tienes, como fecha de creación/actualización --}}
                            <div>
                                <span class="font-semibold text-gray-600 dark:text-gray-400">Registrada:</span>
                                <p class="text-gray-900 dark:text-gray-100">{{ $editoriales->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
    
                        {{-- Botones de Acción y Volver --}}
                        <div class="flex items-center justify-between mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                            {{-- Botón Volver --}}
                            {{-- Ruta verificada: admin.editoriales.index --}}
                            <a href="{{ route('admin.editoriales.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                {{ __('Volver a la Lista') }}
                            </a>
    
                            {{-- Grupo de botones Editar y Eliminar --}}
                            <div class="flex space-x-2">
                                {{-- Botón Editar --}}
                                {{-- Ruta verificada: admin.editoriales.edit --}}
                                <a href="{{ route('admin.editoriales.edit', $editoriales) }}"
                                   title="Editar"
                                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-xs font-medium text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 dark:focus:ring-offset-gray-800">
                                    Editar
                                </a>
    
                                {{-- Botón Eliminar --}}
                                {{-- Ruta verificada: admin.editoriales.destroy --}}
                                <form method="POST" action="{{ route('admin.editoriales.destroy', $editoriales) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            title="Eliminar"
                                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-xs font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-800"
                                            onclick="return confirm('¿Estás seguro de que quieres eliminar a {{ $editoriales->nombre }}?')">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
    
                    </div>
                </div>
            </div>
        </div>
    </x-app-layout> {{-- ***** MODIFICADO: Cierre de <x-app-layout> ***** --}}
    