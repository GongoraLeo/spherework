{{-- resources/views/libros/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Detalles del Libro') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Card for Book Details --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- ... (Detalles del libro y botón Añadir al Carrito sin cambios) ... --}}
                    {{-- Book Title --}}
                    <h1 class="text-3xl font-bold mb-4">{{ $libros->titulo }}</h1>

                    {{-- Book Details Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <p class="mb-2"><strong class="font-semibold">Autor:</strong> {{ $libros->autor?->nombre ?? 'Desconocido' }}</p>
                            <p class="mb-2"><strong class="font-semibold">Editorial:</strong> {{ $libros->editorial?->nombre ?? 'Desconocida' }}</p>
                            <p class="mb-2"><strong class="font-semibold">ISBN:</strong> {{ $libros->isbn }}</p>
                        </div>
                        <div>
                            <p class="mb-2"><strong class="font-semibold">Año de Publicación:</strong> {{ $libros->anio_publicacion }}</p>
                            <p class="mb-2 text-xl font-bold text-blue-600 dark:text-blue-400">
                                <strong class="font-semibold text-gray-900 dark:text-gray-100">Precio:</strong> {{ number_format($libros->precio, 2, ',', '.') }} €
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">ID: {{ $libros->id }}</p>
                        </div>
                    </div>

                    {{-- Add to Cart Form --}}
                    <div class="mb-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                        @auth
                            <form method="POST" action="{{ route('detallespedidos.store') }}">
                                @csrf
                                <input type="hidden" name="libro_id" value="{{ $libros->id }}">
                                <input type="hidden" name="cantidad" value="1">
                                <input type="hidden" name="precio" value="{{ $libros->precio }}">
                                <button type="submit"
                                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    Añadir al Carrito
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}"
                               class="w-full sm:w-auto text-center inline-flex items-center justify-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Inicia sesión para comprar
                            </a>
                        @endauth
                    </div>

                    {{-- Action Buttons (Back, Edit, Delete) --}}
                    <div class="flex flex-wrap gap-2 border-t border-gray-200 dark:border-gray-700 pt-6">
                        <a href="{{ route('libros.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Volver al Catálogo
                        </a>
                        @auth
                            @if(Auth::user()->rol === 'administrador')
                                <a href="{{ route('libros.edit', $libros) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    Editar
                                </a>
                                <form method="POST" action="{{ route('libros.destroy', $libros) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150" onclick="return confirm('¿Estás seguro de que quieres eliminar el libro \'{{ $libros->titulo }}\'?')">
                                        Eliminar
                                    </button>
                                </form>
                            @endif
                        @endauth
                    </div>
                </div>
            </div> {{-- End Book Details Card --}}

            {{-- ***** START: Comments Section ***** --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h2 class="text-2xl font-semibold mb-6">Comentarios y Valoraciones</h2>

                    {{-- Display Existing Comments --}}
                    <div class="space-y-6">
                        @forelse ($libros->comentarios as $comentario)
                            <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                                <div class="flex items-start justify-between mb-2"> {{-- Changed to items-start for better alignment with rating --}}
                                    <div class="flex items-center">
                                        {{-- User Avatar (Optional) --}}
                                        {{-- <img class="h-8 w-8 rounded-full mr-3" src="{{ $comentario->user->profile_photo_url ?? asset('images/default-avatar.png') }}" alt="{{ $comentario->user->name ?? 'Usuario' }}"> --}}
                                        <div>
                                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $comentario->user->name ?? 'Usuario desconocido' }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400" title="{{ $comentario->created_at->format('d/m/Y H:i:s') }}">
                                                {{ $comentario->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                    {{-- ***** MODIFICADO AQUÍ: Mostrar Puntuación si existe ***** --}}
                                    @if($comentario->puntuacion)
                                        <div class="text-yellow-500 text-sm flex items-center">
                                            {{-- Muestra estrellas llenas y vacías --}}
                                            @for ($i = 1; $i <= 5; $i++)
                                                @if ($i <= $comentario->puntuacion)
                                                    <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                                @else
                                                    <svg class="w-4 h-4 fill-current text-gray-300 dark:text-gray-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                                @endif
                                            @endfor
                                            <span class="ml-1">({{ $comentario->puntuacion }})</span>
                                        </div>
                                    @endif
                                    {{-- ***** FIN MODIFICACIÓN ***** --}}
                                </div>
                                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $comentario->comentario }}</p>
                                {{-- ... (Botón eliminar comentario opcional) ... --}}
                            </div>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400">Aún no hay comentarios para este libro. ¡Sé el primero!</p>
                        @endforelse
                    </div>

                    {{-- Add Comment Form (Only for logged-in users) --}}
                    @auth
                        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold mb-4">Deja tu comentario y valoración</h3>
                            <form action="{{ route('comentarios.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="libro_id" value="{{ $libros->id }}">

                                {{-- ***** MODIFICADO AQUÍ: Añadir campo de Puntuación ***** --}}
                                <div class="mb-4">
                                    <x-input-label for="puntuacion" :value="__('Tu valoración (opcional)')" />
                                    <div class="flex items-center space-x-2 mt-1" x-data="{ rating: {{ old('puntuacion', 0) }} }">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <label for="puntuacion_{{ $i }}" class="cursor-pointer">
                                                <input type="radio" name="puntuacion" id="puntuacion_{{ $i }}" value="{{ $i }}" class="sr-only" x-model="rating">
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
                                {{-- ***** FIN MODIFICACIÓN ***** --}}

                                <div class="mb-4">
                                    <x-input-label for="texto" :value="__('Comentario')" /> {{-- Label visible es mejor --}}
                                    <textarea name="texto" id="texto" rows="4" required
                                              class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                              placeholder="Escribe tu comentario aquí..."
                                    >{{ old('texto') }}</textarea>
                                    <x-input-error :messages="$errors->get('texto')" class="mt-2" />
                                </div>

                                <div class="flex justify-end">
                                    <x-primary-button>
                                        {{ __('Publicar') }} {{-- Texto más corto --}}
                                    </x-primary-button>
                                </div>
                            </form>
                        </div>
                    @else
                        <p class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700 text-center text-gray-600 dark:text-gray-400">
                            <a href="{{ route('login') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Inicia sesión</a> para dejar un comentario y valoración.
                        </p>
                    @endauth

                </div>
            </div>
            {{-- ***** END: Comments Section ***** --}}

        </div>
    </div>
    {{-- Alpine.js es necesario para la interacción de las estrellas en el formulario --}}
    {{-- Asegúrate de que Alpine.js esté incluido en tu layout principal o impórtalo aquí si no lo está --}}
    {{-- <script src="//unpkg.com/alpinejs" defer></script> --}}
</x-app-layout>
