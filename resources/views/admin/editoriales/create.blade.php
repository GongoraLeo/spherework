{{-- filepath: resources/views/admin/editoriales/create.blade.php --}}
{{-- ***** MODIFICADO: Cambiado @extends por <x-app-layout> ***** --}}
    <x-app-layout>

        {{-- Slot para el header (opcional) --}}
        {{-- <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                {{ __('Crear Nueva Editorial') }}
            </h2>
        </x-slot> --}}
    
        <div class="py-12">
            <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
    
                        <h1 class="text-2xl font-semibold mb-6">Crear Nueva Editorial</h1>
    
                        {{-- Mostrar errores de validación --}}
                        @if ($errors->any())
                            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                <strong class="font-bold">¡Error de validación!</strong>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
    
                        {{-- Formulario de creación --}}
                        {{-- Ruta verificada: admin.editoriales.store --}}
                        <form method="POST" action="{{ route('admin.editoriales.store') }}">
                            @csrf
    
                            {{-- Campo Nombre --}}
                            <div class="mb-4">
                                <label for="nombre" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Nombre de la Editorial</label>
                                <input id="nombre"
                                       class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                       type="text"
                                       name="nombre"
                                       value="{{ old('nombre') }}"
                                       required
                                       autofocus />
                                @error('nombre') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                            </div>
    
                            {{-- Campo País --}}
                            <div class="mb-4">
                                <label for="pais" class="block font-medium text-sm text-gray-700 dark:text-gray-300">País</label>
                                <input id="pais"
                                       class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                       type="text"
                                       name="pais"
                                       value="{{ old('pais') }}"
                                       required />
                                @error('pais') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                            </div>
    
                            {{-- Botones --}}
                            <div class="flex items-center justify-end mt-6">
                                {{-- Ruta verificada: admin.editoriales.index --}}
                                <a href="{{ route('admin.editoriales.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                    Cancelar
                                </a>
                                <button type="submit" class="ms-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    Guardar Editorial
                                </button>
                            </div>
                        </form>
    
                    </div>
                </div>
            </div>
        </div>
    </x-app-layout> {{-- ***** MODIFICADO: Cierre de <x-app-layout> ***** --}}
    