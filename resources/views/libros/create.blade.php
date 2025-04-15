{{-- filepath: resources/views/libros/create.blade.php --}}
<x-app-layout>
    {{-- Contenedor principal --}}
    <div class="py-12">
        {{-- Usamos max-w-2xl porque el formulario tiene más campos --}}
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            {{-- La "tarjeta" visual --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <h1 class="text-2xl font-semibold mb-6">Añadir Nuevo Libro</h1>

                    {{-- Formulario para crear el libro --}}
                    <form method="POST" action="{{ route('libros.store') }}">
                        @csrf

                        {{-- Campo Título --}}
                        <div class="mb-4">
                            <x-input-label for="titulo" :value="__('Título')" />
                            <x-text-input id="titulo" class="block mt-1 w-full" type="text" name="titulo" :value="old('titulo')" required autofocus />
                            <x-input-error :messages="$errors->get('titulo')" class="mt-2" />
                        </div>

                        {{-- Campo ISBN --}}
                        <div class="mb-4">
                            <x-input-label for="isbn" :value="__('ISBN')" />
                            <x-text-input id="isbn" class="block mt-1 w-full" type="text" name="isbn" :value="old('isbn')" required />
                            <x-input-error :messages="$errors->get('isbn')" class="mt-2" />
                        </div>

                        {{-- Fila para Año y Precio (usando grid) --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            {{-- Campo Año Publicación --}}
                            <div>
                                <x-input-label for="anio_publicacion" :value="__('Año Publicación')" />
                                <x-text-input id="anio_publicacion" class="block mt-1 w-full" type="number" name="anio_publicacion" :value="old('anio_publicacion')" required min="1000" :max="date('Y')" />
                                <x-input-error :messages="$errors->get('anio_publicacion')" class="mt-2" />
                            </div>

                            {{-- Campo Precio --}}
                            <div>
                                <x-input-label for="precio" :value="__('Precio (€)')" />
                                <x-text-input id="precio" class="block mt-1 w-full" type="number" name="precio" :value="old('precio')" required step="0.01" min="0" />
                                <x-input-error :messages="$errors->get('precio')" class="mt-2" />
                            </div>
                        </div>

                        {{-- Campo Autor (Select) --}}
                        <div class="mb-4">
                            <x-input-label for="autor_id" :value="__('Autor')" />
                            <select id="autor_id" name="autor_id" required class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="" disabled {{ old('autor_id') ? '' : 'selected' }}>-- Selecciona un autor --</option>
                                {{-- Itera sobre los autores pasados desde el controlador --}}
                                @foreach ($autores as $autor)
                                    <option value="{{ $autor->id }}" {{ old('autor_id') == $autor->id ? 'selected' : '' }}>
                                        {{ $autor->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('autor_id')" class="mt-2" />
                        </div>

                        {{-- Campo Editorial (Select) --}}
                        <div class="mb-4">
                            <x-input-label for="editorial_id" :value="__('Editorial')" />
                            <select id="editorial_id" name="editorial_id" required class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="" disabled {{ old('editorial_id') ? '' : 'selected' }}>-- Selecciona una editorial --</option>
                                {{-- Itera sobre las editoriales pasadas desde el controlador --}}
                                @foreach ($editoriales as $editorial)
                                    <option value="{{ $editorial->id }}" {{ old('editorial_id') == $editorial->id ? 'selected' : '' }}>
                                        {{ $editorial->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('editorial_id')" class="mt-2" />
                        </div>

                        {{-- Botones de Acción --}}
                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('libros.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                {{ __('Cancelar') }}
                            </a>

                            <x-primary-button class="ms-4">
                                {{ __('Guardar Libro') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div> {{-- Cierre de div.p-6 --}}
            </div> {{-- Cierre de div.bg-white --}}
        </div> {{-- Cierre de div.max-w-2xl --}}
    </div> {{-- Cierre de div.py-12 --}}
</x-app-layout>
