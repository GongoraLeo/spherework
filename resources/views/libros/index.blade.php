{{-- filepath: resources/views/libros/index.blade.php --}}
<x-app-layout>
    

    {{-- Mensajes de éxito/error --}}
    @if (session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    {{-- ***** INICIO: Mensaje de Bienvenida ***** --}}
    <div class="mb-6 p-4 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg text-center">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
            ¡Bienvenido/a a SphereWork!
        </h2>
        <br>
        <p class="text-left text-gray-600 dark:text-gray-400 mt-1">
            ¡Bienvenido a SphereWorks, tu librería online de confianza! Aquí encontrarás un universo de historias, conocimiento e inspiración al alcance de un clic. Nos apasionan los libros y queremos compartir contigo una cuidada selección de títulos que van desde los grandes clásicos hasta las últimas novedades editoriales. Ya seas amante de la ficción, la no ficción, los libros técnicos o los cuentos infantiles, en SphereWorks tenemos algo para ti.
        </p>
        <p class="text-left text-gray-600 dark:text-gray-400 mt-1">
            Además, contamos con herramientas pensadas para mejorar tu experiencia: valoraciones y comentarios de otros lectores, recomendaciones personalizadas y una gestión sencilla de tus pedidos. Navega, descubre y déjate llevar por las palabras.
        </p>
        <p class="text-left text-gray-600 dark:text-gray-400 mt-1">
            Nuestro compromiso es ofrecerte un servicio cercano, rápido y seguro, porque sabemos que cada libro es una puerta abierta a nuevos mundos. Gracias por formar parte de esta comunidad lectora.
        </p>
        <p class="text-gray-600 dark:text-gray-400 mt-1">
            ¡Empieza tu próxima aventura literaria con nosotros!
        </p>
        
        {{-- Puedes añadir más elementos aquí si lo deseas, como un enlace a "Novedades" o "Más vendidos" --}}
    </div>
    {{-- ***** FIN: Mensaje de Bienvenida ***** --}}

    {{-- Encabezado con Título y Botón Crear --}}
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Catálogo de Libros</h1>

        {{-- Botón Crear Libro (Solo para administradores) --}}
        @auth
            {{-- Comprueba si el rol del usuario es 'administrador' --}}
            @if(Auth::user()->rol === 'administrador')
                <a href="{{ route('libros.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    {{ __('Añadir Nuevo Libro') }}
                </a>
            @endif
        @endauth
    </div>

    {{-- Contenedor tipo tarjeta/lista para los libros --}}
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            {{-- Rejilla para mostrar libros --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                @forelse ($libros as $libro)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 flex flex-col justify-between">
                        {{-- Detalles del Libro --}}
                        <div>
                            <h2 class="text-lg font-semibold mb-2">{{ $libro->titulo }}</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                                Autor: {{ $libro->autor?->nombre ?? 'Desconocido' }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                                Editorial: {{ $libro->editorial?->nombre ?? 'Desconocida' }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">ISBN: {{ $libro->isbn }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Año: {{ $libro->anio_publicacion }}</p>
                            <p class="text-lg font-bold text-blue-600 dark:text-blue-400 mb-4">
                                Precio: {{ number_format($libro->precio, 2, ',', '.') }} €
                            </p>
                        </div>

                        {{-- Formulario "Añadir al Carrito" --}}
                        <div class="mt-auto mb-4"> {{-- Añadido mb-4 para separar del CRUD --}}
                            @auth
                                <form method="POST" action="{{ route('detallespedidos.store') }}">
                                    @csrf
                                    <input type="hidden" name="libro_id" value="{{ $libro->id }}">
                                    <input type="hidden" name="cantidad" value="1">
                                    <input type="hidden" name="precio" value="{{ $libro->precio }}">
                                    <button type="submit"
                                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                        Añadir al Carrito
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('login') }}"
                                   class="w-full text-center inline-flex items-center justify-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    Inicia sesión para comprar
                                </a>
                            @endauth
                        </div>

                         {{-- Botones CRUD (Protegidos por rol) --}}
                         <div class="flex space-x-2 justify-center border-t border-gray-200 dark:border-gray-700 pt-3">
                             {{-- Botón Ver (Visible para todos los logueados) --}}
                             @auth
                                <a href="{{ route('libros.show', $libro) }}" title="Ver Detalles" class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 dark:border-gray-600 shadow-sm text-xs font-medium rounded text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    Ver
                                </a>
                             @endauth

                             {{-- Botones Editar y Eliminar (Solo para administradores) --}}
                             @auth
                                 {{-- Comprueba si el rol del usuario es 'administrador' --}}
                                 @if(Auth::user()->rol === 'administrador')
                                     {{-- Botón Editar --}}
                                     <a href="{{ route('libros.edit', $libro) }}" title="Editar Libro" class="inline-flex items-center px-2.5 py-1.5 border border-transparent rounded shadow-sm text-xs font-medium text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                         Editar
                                     </a>

                                     {{-- Botón Eliminar --}}
                                     <form method="POST" action="{{ route('libros.destroy', $libro) }}" class="inline">
                                         @csrf
                                         @method('DELETE')
                                         <button type="submit" title="Eliminar Libro" class="inline-flex items-center px-2.5 py-1.5 border border-transparent rounded shadow-sm text-xs font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" onclick="return confirm('¿Estás seguro de que quieres eliminar el libro \'{{ $libro->titulo }}\'?')">
                                             Eliminar
                                         </button>
                                     </form>
                                 @endif
                             @endauth
                         </div>
                    </div>
                @empty
                    <p class="col-span-full text-center text-gray-500 dark:text-gray-400">No hay libros disponibles en este momento.</p>
                @endforelse

            </div> {{-- Fin de la rejilla --}}

            {{-- Paginación (si la estás usando en el controlador) --}}
            {{-- Asegúrate de que $libros sea una instancia de Paginator --}}
            @if ($libros instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="mt-8">
                    {{ $libros->links() }}
                </div>
            @endif

        </div>
    </div>

</x-app-layout>
