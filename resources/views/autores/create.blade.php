{{-- filepath: resources/views/autores/create.blade.php --}}
<x-app-layout>
    {{-- Contenedor principal --}}
    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            {{-- La "tarjeta" visual --}}
            <div class="bg-white dark:bg-gray-200 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Título --}}
                    <h1 class="text-2xl font-semibold mb-6 text-gray-900 dark:text-gray-900">Crear Nuevo Autor</h1>

                    {{-- Formulario para crear el autor --}}
                    <form method="POST" action="{{ route('autores.store') }}">
                        @csrf

                        {{-- Campo para el Nombre del Autor --}}
                        <div class="mb-4">
                            <x-input-label for="nombre" :value="__('Nombre del Autor')" class="dark:text-gray-700"/>
                            <x-text-input id="nombre"
                                          class="block mt-1 w-full dark:text-gray-900"
                                          type="text"
                                          name="nombre"
                                          :value="old('nombre')"
                                          required
                                          autofocus />
                            <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                        </div>

                        {{-- NUEVO: Campo para el País del Autor --}}
                        <div class="mb-4">
                            <x-input-label for="pais" :value="__('País del Autor')" class="dark:text-gray-700"/>
                            <x-text-input id="pais"
                                          class="block mt-1 w-full dark:text-gray-900"
                                          type="text"
                                          name="pais"
                                          :value="old('pais')" {{-- Importante para mantener el valor si hay error --}}
                                          required /> {{-- Asumiendo que el país es requerido --}}
                            {{-- Muestra errores de validación para el campo 'pais' --}}
                            <x-input-error :messages="$errors->get('pais')" class="mt-2" />
                        </div>
                        {{-- FIN NUEVO CAMPO --}}

                        {{-- Botones de Acción --}}
                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('autores.index') }}" class="text-sm text-gray-600 dark:text-gray-700 hover:text-gray-900 dark:hover:text-black rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-200">
                                {{ __('Cancelar') }}
                            </a>

                            <x-primary-button class="ms-4">
                                {{ __('Guardar Autor') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div> {{-- Cierre de div.p-6 --}}
            </div> {{-- Cierre de div.bg-white --}}
        </div> {{-- Cierre de div.max-w-xl --}}
    </div> {{-- Cierre de div.py-12 --}}
</x-app-layout>
