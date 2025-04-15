{{-- filepath: resources/views/autores/edit.blade.php --}}
<x-app-layout>
    {{-- Contenedor principal --}}
    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            {{-- La "tarjeta" visual --}}
            {{-- Mantenemos el fondo claro en modo oscuro para consistencia con create.blade.php --}}
            <div class="bg-white dark:bg-gray-200 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Título - Indicamos qué autor se edita --}}
                    <h1 class="text-2xl font-semibold mb-6 text-gray-900 dark:text-gray-900">Editar Autor: {{ $autores->nombre }}</h1>

                    {{-- Formulario para editar el autor --}}
                    {{-- La acción apunta a la ruta 'update', pasando el objeto $autores --}}
                    <form method="POST" action="{{ route('autores.update', $autores) }}">
                        @csrf {{-- Protección CSRF --}}
                        @method('PUT') {{-- Método HTTP para actualización (puede ser 'PATCH' también) --}}

                        {{-- Campo para el Nombre del Autor --}}
                        <div class="mb-4">
                            <x-input-label for="nombre" :value="__('Nombre del Autor')" class="dark:text-gray-700"/>
                            <x-text-input id="nombre"
                                          class="block mt-1 w-full dark:text-gray-900"
                                          type="text"
                                          name="nombre"
                                          {{-- Valor: usa el valor antiguo si existe (error de validación), si no, usa el valor actual del modelo $autores --}}
                                          :value="old('nombre', $autores->nombre)"
                                          required
                                          autofocus />
                            <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                        </div>

                        {{-- Campo para el País del Autor --}}
                        <div class="mb-4">
                            <x-input-label for="pais" :value="__('País del Autor')" class="dark:text-gray-700"/>
                            <x-text-input id="pais"
                                          class="block mt-1 w-full dark:text-gray-900"
                                          type="text"
                                          name="pais"
                                          {{-- Valor: usa el valor antiguo si existe, si no, usa el valor actual del modelo $autores --}}
                                          :value="old('pais', $autores->pais)"
                                          required />
                            <x-input-error :messages="$errors->get('pais')" class="mt-2" />
                        </div>

                        {{-- Botones de Acción --}}
                        <div class="flex items-center justify-end mt-6">
                            {{-- Botón Cancelar: puede ir al índice o a la vista show del autor --}}
                            <a href="{{ route('autores.index') }}" class="text-sm text-gray-600 dark:text-gray-700 hover:text-gray-900 dark:hover:text-black rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-200">
                                {{ __('Cancelar') }}
                            </a>
                            {{-- Alternativa: Cancelar y volver a la vista show
                            <a href="{{ route('autores.show', $autores) }}" class="...">
                                {{ __('Cancelar') }}
                            </a>
                            --}}

                            {{-- Botón primario para Guardar Cambios --}}
                            <x-primary-button class="ms-4">
                                {{ __('Guardar Cambios') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div> {{-- Cierre de div.p-6 --}}
            </div> {{-- Cierre de div.bg-white --}}
        </div> {{-- Cierre de div.max-w-xl --}}
    </div> {{-- Cierre de div.py-12 --}}
</x-app-layout>
