{{-- resources/views/libros/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar Libro') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <h3 class="text-2xl font-semibold mb-6">Editando: {{ $libros->titulo }}</h3>

                    {{-- Mostrar errores de validación generales --}}
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">¡Error!</strong>
                            <span class="block sm:inline">Por favor, corrige los siguientes errores:</span>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li class="list-disc ml-5">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('libros.update', $libros) }}">
                        @csrf
                        @method('PUT')

                        {{-- Campo Título --}}
                        <div class="mb-4">
                            <x-input-label for="titulo" :value="__('Título')" />
                            {{-- Añadida clase dark:text-gray-400 --}}
                            <x-text-input id="titulo" class="block mt-1 w-full dark:text-gray-400" type="text" name="titulo" :value="old('titulo', $libros->titulo)" required autofocus autocomplete="off" />
                            <x-input-error :messages="$errors->get('titulo')" class="mt-2" />
                        </div>

                        {{-- Campo ISBN --}}
                        <div class="mb-4">
                            <x-input-label for="isbn" :value="__('ISBN')" />
                            {{-- Añadida clase dark:text-gray-400 --}}
                            <x-text-input id="isbn" class="block mt-1 w-full dark:text-gray-400" type="text" name="isbn" :value="old('isbn', $libros->isbn)" required autocomplete="off" />
                            <x-input-error :messages="$errors->get('isbn')" class="mt-2" />
                        </div>

                        {{-- Campo Año de Publicación --}}
                        <div class="mb-4">
                            <x-input-label for="anio_publicacion" :value="__('Año de Publicación')" />
                            {{-- Añadida clase dark:text-gray-400 --}}
                            <x-text-input id="anio_publicacion" class="block mt-1 w-full dark:text-gray-400" type="number" name="anio_publicacion" :value="old('anio_publicacion', $libros->anio_publicacion)" required min="1000" max="{{ date('Y') }}" step="1" />
                            <x-input-error :messages="$errors->get('anio_publicacion')" class="mt-2" />
                        </div>

                        {{-- Campo Autor (Select) --}}
                        <div class="mb-4">
                            <x-input-label for="autor_id" :value="__('Autor')" />
                            {{-- Cambiada clase dark:text-gray-300 a dark:text-gray-400 --}}
                            <select name="autor_id" id="autor_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="">Selecciona un autor</option>
                                @foreach ($autores as $autor)
                                    <option value="{{ $autor->id }}" {{ old('autor_id', $libros->autor_id) == $autor->id ? 'selected' : '' }}>
                                        {{ $autor->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('autor_id')" class="mt-2" />
                        </div>

                        {{-- Campo Editorial (Select) --}}
                        <div class="mb-4">
                            <x-input-label for="editorial_id" :value="__('Editorial')" />
                            {{-- Cambiada clase dark:text-gray-300 a dark:text-gray-400 --}}
                            <select name="editorial_id" id="editorial_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="">Selecciona una editorial</option>
                                @foreach ($editoriales as $editorial)
                                    <option value="{{ $editorial->id }}" {{ old('editorial_id', $libros->editorial_id) == $editorial->id ? 'selected' : '' }}>
                                        {{ $editorial->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('editorial_id')" class="mt-2" />
                        </div>

                        {{-- Campo Precio --}}
                        <div class="mb-6">
                            <x-input-label for="precio" :value="__('Precio (€)')" />
                            {{-- Añadida clase dark:text-gray-400 --}}
                            <x-text-input id="precio" class="block mt-1 w-full dark:text-gray-400" type="number" name="precio" :value="old('precio', $libros->precio)" required step="0.01" min="0" />
                            <x-input-error :messages="$errors->get('precio')" class="mt-2" />
                        </div>

                        {{-- Botones de Acción --}}
                        <div class="flex items-center justify-end mt-6 space-x-4">
                             <a href="{{ route('libros.show', $libros) }}" class="inline-flex items-center px-4 py-2 bg-gray-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                {{ __('Cancelar') }}
                            </a>

                            <x-primary-button>
                                {{ __('Actualizar Libro') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
