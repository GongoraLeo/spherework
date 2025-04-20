{{-- filepath: resources/views/comentarios/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Editar Comentario') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8"> {{-- Max width más adecuado para un formulario --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <h3 class="text-lg font-medium mb-2">Editando comentario para el libro:</h3>
                    {{-- Mostrar el título del libro (si la relación está cargada) --}}
                    <p class="mb-6 text-indigo-600 dark:text-indigo-400 font-semibold">
                        @if($comentarios->libro)
                            <a href="{{ route('libros.show', $comentarios->libro) }}" class="hover:underline">
                                {{ $comentarios->libro->titulo }}
                            </a>
                        @else
                            Libro no disponible
                        @endif
                    </p>

                    {{-- Formulario de Edición --}}
                    <form method="POST" action="{{ route('comentarios.update', $comentarios) }}">
                        @csrf
                        @method('PATCH') {{-- O PUT si prefieres, pero PATCH es más común para actualizaciones parciales --}}

                        {{-- Campo Puntuación (reutilizado de libros.show) --}}
                        <div class="mb-4">
                            <x-input-label for="puntuacion" :value="__('Tu valoración')" />
                            {{-- Usamos old() para mantener el valor si falla la validación, si no, usamos el valor actual del comentario --}}
                            <div class="flex items-center space-x-2 mt-1" x-data="{ rating: {{ old('puntuacion', $comentarios->puntuacion ?? 0) }} }">
                                @for ($i = 1; $i <= 5; $i++)
                                    <label for="puntuacion_{{ $i }}" class="cursor-pointer">
                                        <input type="radio" name="puntuacion" id="puntuacion_{{ $i }}" value="{{ $i }}" class="sr-only" x-model="rating" {{ (old('puntuacion', $comentarios->puntuacion) == $i) ? 'checked' : '' }}>
                                        <svg class="w-6 h-6 fill-current" :class="rating >= {{ $i }} ? 'text-yellow-500' : 'text-gray-300 dark:text-gray-600 hover:text-yellow-400'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                        </svg>
                                    </label>
                                @endfor
                                {{-- Botón opcional para limpiar la selección --}}
                                <button type="button" @click="rating = 0; document.querySelectorAll('input[name=puntuacion]').forEach(el => el.checked = false)" x-show="rating > 0" class="text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 ml-2">Limpiar</button>
                            </div>
                            <x-input-error :messages="$errors->get('puntuacion')" class="mt-2" />
                        </div>

                        {{-- Campo Texto del Comentario --}}
                        <div class="mb-6"> {{-- Aumentado margen inferior --}}
                            <x-input-label for="texto" :value="__('Comentario')" />
                            <textarea name="texto" id="texto" rows="5" required
                                      class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                      placeholder="Escribe tu comentario aquí..."
                            >{{ old('texto', $comentarios->comentario) }}</textarea> {{-- Usamos old() o el valor actual --}}
                            <x-input-error :messages="$errors->get('texto')" class="mt-2" />
                        </div>

                        {{-- Botones de Acción --}}
                        <div class="flex items-center justify-end space-x-4">
                            {{-- Botón Cancelar (vuelve al perfil o al libro) --}}
                            <a href="{{ route('profile.show') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                Cancelar
                            </a>
                            {{-- Botón Guardar Cambios --}}
                            <x-primary-button>
                                {{ __('Guardar Cambios') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
    {{-- Alpine.js es necesario para la interacción de las estrellas --}}
    {{-- <script src="//unpkg.com/alpinejs" defer></script> --}}
</x-app-layout>
