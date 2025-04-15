<!-- filepath: c:\xampp\htdocs\spherework\resources\views\autores\index.blade.php -->
<x-app-layout>

    {{-- Encabezado con Título y Botón Crear --}}
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Lista de Autores</h1>
        <a href="{{ route('autores.create') }}"
           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
            {{ __('Crear Nuevo Autor') }}
        </a>
    </div>

    {{-- Contenedor tipo tarjeta para la lista --}}
    {{-- Aseguramos fondo oscuro estándar y texto claro por defecto dentro de la tarjeta en modo oscuro --}}
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            {{-- Lista de autores --}}
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                {{-- Usar @forelse para manejar el caso de que no haya autores --}}
                @forelse ($autores as $autor)
                    {{-- Usamos flex para alinear nombre y botones --}}
                    <li class="py-3 flex justify-between items-center">
                        {{-- Nombre del autor - Aseguramos texto oscuro si el fondo fuera claro en dark mode, pero con dark:bg-gray-800, dark:text-gray-100 debería funcionar. Añadimos explícitamente por si acaso. --}}
                        <span class="text-gray-900 dark:text-gray-100">{{ $autor->nombre }} - <span class="text-sm text-gray-600 dark:text-gray-400">{{ $autor->pais }}</span></span>

                        {{-- Contenedor para los botones de acción --}}
                        <div class="flex space-x-2">
                            {{-- Botón Mostrar (Show) --}}
                            <a href="{{ route('autores.show', $autor) }}"
                               title="Ver Detalles"
                               class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 dark:border-gray-600 shadow-sm text-xs font-medium rounded text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                Ver
                            </a>

                            {{-- Botón Editar (Edit) --}}
                            <a href="{{ route('autores.edit', $autor) }}"
                               title="Editar"
                               class="inline-flex items-center px-2.5 py-1.5 border border-transparent rounded shadow-sm text-xs font-medium text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 dark:focus:ring-offset-gray-800">
                                Editar
                            </a>

                            {{-- Botón Eliminar (Destroy) - Usa un formulario --}}
                            <form method="POST" action="{{ route('autores.destroy', $autor) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        title="Eliminar"
                                        class="inline-flex items-center px-2.5 py-1.5 border border-transparent rounded shadow-sm text-xs font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-800"
                                        {{-- Confirmación Javascript para seguridad --}}
                                        onclick="return confirm('¿Estás seguro de que quieres eliminar a {{ $autor->nombre }}?')">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </li>
                @empty
                    {{-- Mensaje si no hay autores --}}
                    <li class="py-3 text-gray-500 dark:text-gray-400">No hay autores registrados.</li>
                @endforelse
            </ul>
        </div>
    </div>

</x-app-layout>
