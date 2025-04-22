<?php
// filepath: app/Http/Controllers/LibrosController.php

namespace App\Http\Controllers;

use App\Models\Libros; // Modelo Eloquent para interactuar con la tabla 'libros'.
use Illuminate\Http\Request; // Objeto para manejar las solicitudes HTTP entrantes.
use App\Models\Autores; // Modelo Autores, necesario para los formularios create/edit.
use App\Models\Editoriales; // Modelo Editoriales, necesario para los formularios create/edit.
use Illuminate\Support\Facades\Auth; // Fachada para verificar autenticación y rol del usuario.
use Illuminate\View\View;             // Para especificar el tipo de retorno de vistas.
use Illuminate\Http\RedirectResponse; // Para especificar el tipo de retorno de redirecciones.
use Illuminate\Support\Facades\Log;    // Fachada para registrar errores (implícitamente usada en manejo de excepciones).
use Illuminate\Validation\Rule;       // Clase para reglas de validación (usada en update para 'unique').

/**
 * Class LibrosController
 *
 * Controlador encargado de gestionar las operaciones CRUD (Crear, Leer, Actualizar, Eliminar)
 * para el recurso 'Libros'. La visualización del índice y detalles es pública,
 * mientras que las operaciones de creación, edición, actualización y eliminación
 * están restringidas a usuarios con el rol 'administrador'.
 *
 * @package App\Http\Controllers
 */
class LibrosController extends Controller
{
    /**
     * Muestra una lista paginada de todos los libros disponibles (catálogo).
     *
     * Esta vista es pública y accesible para cualquier visitante. Utiliza Eager Loading
     * con `with(['autor', 'editorial'])` para cargar las relaciones 'autor' y 'editorial'
     * de forma eficiente junto con los libros, evitando así consultas N+1 en la vista.
     * Los libros se obtienen ordenados por fecha de creación descendente (`latest()`)
     * y se paginan (`paginate(15)`) para mostrar 15 libros por página.
     * Finalmente, renderiza la vista 'libros.index' pasándole la colección paginada de libros.
     *
     * @return \Illuminate\View\View Retorna la vista 'libros.index' con la lista de libros.
     */
    public function index(): View
    {
        // 1. Obtención de Datos con Eager Loading:
        // Se utiliza `with(['autor', 'editorial'])` para cargar las relaciones especificadas
        // junto con los libros en una consulta optimizada.
        // `latest()` ordena por 'created_at' descendente (libros más nuevos primero).
        // `paginate(15)` divide los resultados en páginas de 15 libros.
        $libros = Libros::with(['autor', 'editorial'])->latest()->paginate(15);

        // 2. Retornar la Vista del Catálogo:
        // Renderiza 'resources/views/libros/index.blade.php'.
        // Pasa la colección paginada de libros a la vista usando `compact()`.
        return view('libros.index', compact('libros'));
    }

    /**
     * Muestra el formulario para crear un nuevo libro.
     *
     * Esta acción está restringida a usuarios administradores. Primero, verifica si el usuario
     * está autenticado (`Auth::check()`) y si su rol es 'administrador' (`Auth::user()->rol`).
     * Si no cumple los requisitos, redirige al índice público de libros con un mensaje de error.
     * Si está autorizado, obtiene todas las instancias de `Autores` y `Editoriales`, ordenadas
     * por nombre, para poblar los campos 'select' en el formulario.
     * Finalmente, renderiza la vista 'libros.create', pasándole las colecciones de autores y editoriales.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse Retorna la vista 'libros.create' o redirige si no es admin.
     */
    public function create(): View|RedirectResponse
    {
        // 1. Autorización: Verifica si el usuario está autenticado y es administrador.
        if (!Auth::check() || Auth::user()->rol !== 'administrador') {
            // Si no cumple, redirige al índice de libros (público) con un mensaje de error.
            return redirect()->route('libros.index')->with('error', 'No tienes permiso para añadir libros.');
            // Alternativa: abort(403, 'Acción no autorizada.'); detendría la ejecución.
        }

        // 2. Obtener Datos para Selects: Recupera autores y editoriales.
        // Se obtienen todos los autores ordenados por nombre.
        $autores = Autores::orderBy('nombre')->get();
        // Se obtienen todas las editoriales ordenadas por nombre.
        $editoriales = Editoriales::orderBy('nombre')->get();

        // 3. Retornar la Vista del Formulario:
        // Renderiza 'resources/views/libros/create.blade.php'.
        // Pasa las colecciones de autores y editoriales para los desplegables.
        return view('libros.create', compact('autores', 'editoriales'));
    }

    /**
     * Almacena un nuevo libro creado en la base de datos.
     *
     * Restringido a administradores. Primero, verifica la autorización del usuario; si no es
     * administrador, detiene la ejecución con `abort(403)`. Luego, valida los datos recibidos
     * del formulario usando `$request->validate()`. Las reglas aseguran que el título, ISBN (único),
     * año de publicación (dentro de un rango), precio (no negativo), ID de autor (existente)
     * y ID de editorial (existente) sean válidos. Si la validación es exitosa, crea un nuevo
     * registro en la tabla 'libros' usando `Libros::create($request->all())`.
     * Finalmente, redirige al índice público de libros (`libros.index`) con un mensaje de éxito.
     * No se incluye manejo explícito de excepciones para la creación, asumiendo que la validación
     * previene la mayoría de los errores.
     *
     * @param  \Illuminate\Http\Request  $request Objeto con los datos del formulario de creación.
     * @return \Illuminate\Http\RedirectResponse Redirige al índice de libros.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Autorización: Verifica rol de administrador.
        if (!Auth::check() || Auth::user()->rol !== 'administrador') {
             // Detiene la ejecución si no está autorizado.
             abort(403, 'Acción no autorizada.');
        }

        // 2. Validación de Datos:
        // Se utiliza el método `validate` heredado del Controller base.
        $request->validate([
            'titulo' => 'required|string|max:255', // Título obligatorio.
            'isbn' => 'required|string|max:13|unique:libros,isbn', // ISBN obligatorio, único en la tabla 'libros'.
            'anio_publicacion' => 'required|integer|min:1000|max:' . date('Y'), // Año obligatorio, entero, dentro de un rango razonable.
            'precio' => 'required|numeric|min:0', // Precio obligatorio, numérico, no negativo.
            'autor_id' => 'required|integer|exists:autores,id', // ID de autor obligatorio, debe existir en la tabla 'autores'.
            'editorial_id' => 'required|integer|exists:editoriales,id', // ID de editorial obligatorio, debe existir en la tabla 'editoriales'.
        ]);

        // 3. Creación del Recurso:
        // Se utiliza el método estático `create` del modelo Libros.
        // `$request->all()` proporciona los datos validados que coinciden con los atributos 'fillable' del modelo.
        Libros::create($request->all());

        // 4. Redirección Éxito:
        // Redirige a la ruta del índice de libros (público) con un mensaje flash de éxito.
        return redirect()->route('libros.index')
            ->with('success', 'Libro añadido correctamente.');
    }


    /**
     * Muestra los detalles de un libro específico.
     *
     * Esta vista es pública. Utiliza Route Model Binding para obtener automáticamente la instancia
     * del libro (`$libros`) correspondiente al ID en la URL. Emplea `loadMissing()` para realizar
     * Lazy Eager Loading: carga las relaciones 'autor', 'editorial' y 'comentarios' (incluyendo
     * la relación anidada 'user' de cada comentario) solo si no han sido cargadas previamente.
     * Esto es eficiente si se accede directamente a la URL del libro.
     * Finalmente, renderiza la vista 'libros.show', pasándole la instancia del libro con sus relaciones cargadas.
     *
     * @param  \App\Models\Libros  $libros Instancia del modelo Libros inyectada por Laravel
     *                                    basada en el parámetro de ruta (ej. /libros/{libro}).
     *                                    Se mantiene el nombre plural `$libros`.
     * @return \Illuminate\View\View Retorna la vista 'libros.show' con los detalles del libro.
     */
    public function show(Libros $libros): View
    {
        // 1. Carga de Relaciones (Lazy Eager Loading):
        // `loadMissing` carga las relaciones especificadas solo si no han sido cargadas previamente.
        // Es útil aquí porque el libro puede venir sin relaciones si se accede directamente a la URL.
        // Carga 'autor', 'editorial', y los 'comentarios' junto con el 'user' de cada comentario.
        $libros->loadMissing(['autor', 'editorial', 'comentarios.user']);

        // 2. Retornar la Vista de Detalles:
        // Renderiza 'resources/views/libros/show.blade.php'.
        // Pasa la instancia del libro `$libros` (con relaciones cargadas) a la vista.
        return view('libros.show', compact('libros'));
    }

    /**
     * Muestra el formulario para editar un libro existente.
     *
     * Restringido a administradores. Verifica la autorización del usuario. Si no es admin,
     * redirige al índice público. Utiliza Route Model Binding para obtener la instancia
     * del libro (`$libros`) a editar. Obtiene listas completas de autores y editoriales,
     * ordenadas por nombre, para poblar los desplegables ('select') del formulario.
     * Renderiza la vista 'libros.edit', pasándole el libro y las listas de autores y editoriales.
     *
     * @param  \App\Models\Libros  $libros Instancia del modelo Libros a editar (Route Model Binding).
     *                                    Se mantiene el nombre plural `$libros`.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse Retorna la vista 'libros.edit' o redirige si no es admin.
     */
    public function edit(Libros $libros): View|RedirectResponse
    {
        // 1. Autorización: Verifica si el usuario es administrador.
        if (!Auth::check() || Auth::user()->rol !== 'administrador') {
             // Redirige al índice público si no tiene permiso.
             return redirect()->route('libros.index')->with('error', 'No tienes permiso para editar libros.');
        }

        // 2. Obtener Datos para Selects: Recupera autores y editoriales.
        $autores = Autores::orderBy('nombre')->get();
        $editoriales = Editoriales::orderBy('nombre')->get();

        // 3. Retornar la Vista del Formulario de Edición:
        // Renderiza 'resources/views/libros/edit.blade.php'.
        // Pasa el libro a editar (`$libros`) y las colecciones de autores y editoriales.
        return view('libros.edit', compact('libros', 'autores', 'editoriales'));
    }

    /**
     * Actualiza un libro existente en la base de datos.
     *
     * Restringido a administradores. Verifica la autorización; si falla, usa `abort(403)`.
     * Valida los datos recibidos del formulario de edición usando `$request->validate()`.
     * La regla 'unique' para el campo 'isbn' se ajusta usando `unique:libros,isbn,' . $libros->id`
     * para asegurar que el ISBN sea único, pero ignorando el registro del libro que se está actualizando.
     * Si la validación es correcta, actualiza el libro usando el método `update()` sobre la instancia
     * `$libros` inyectada por Route Model Binding, pasándole `$request->all()`.
     * Finalmente, redirige al índice público de libros (`libros.index`) con un mensaje de éxito.
     *
     * @param  \Illuminate\Http\Request  $request Datos del formulario de edición.
     * @param  \App\Models\Libros  $libros Instancia del libro a actualizar (Route Model Binding).
     *                                    Se mantiene el nombre plural `$libros`.
     * @return \Illuminate\Http\RedirectResponse Redirige al índice de libros.
     */
    public function update(Request $request, Libros $libros): RedirectResponse
    {
         // 1. Autorización: Verifica rol de administrador.
        if (!Auth::check() || Auth::user()->rol !== 'administrador') {
             abort(403, 'Acción no autorizada.');
        }

        // 2. Validación de Datos (ajustada para update):
        $request->validate([
            'titulo' => 'required|string|max:255',
            'autor_id' => 'required|integer|exists:autores,id',
            'editorial_id' => 'required|integer|exists:editoriales,id',
            'anio_publicacion' => 'required|integer|min:1000|max:' . date('Y'),
            // Regla 'unique' ajustada: verifica unicidad en 'libros', columna 'isbn',
            // ignorando el registro con el ID del libro actual (`$libros->id`).
            'isbn' => 'required|string|max:13|unique:libros,isbn,' . $libros->id,
            'precio' => 'required|numeric|min:0',
        ]);

        // 3. Actualización del Recurso:
        // Se utiliza el método `update()` sobre la instancia `$libros` inyectada.
        $libros->update($request->all());

        // 4. Redirección Éxito:
        // Redirige al índice de libros con un mensaje de éxito.
        // Alternativa: redirigir a la vista show del libro actualizado: route('libros.show', $libros).
        return redirect()->route('libros.index')
            ->with('success', 'Libro actualizado correctamente.');
        // Nota: No se incluye manejo de excepciones explícito, asumiendo que la validación
        // y el Route Model Binding previenen la mayoría de errores.
    }

    /**
     * Elimina un libro específico de la base de datos.
     *
     * Restringido a administradores. Verifica la autorización; si falla, usa `abort(403)`.
     * Intenta eliminar el libro usando `$libros->delete()` dentro de un bloque try-catch.
     * Si la eliminación tiene éxito, redirige al índice de libros con un mensaje de éxito.
     * Si se produce una `QueryException` (comúnmente debido a restricciones de clave foránea,
     * por ejemplo, si el libro existe en la tabla `detallespedidos`), captura esta excepción
     * específicamente, registra el error y redirige al índice con un mensaje de error
     * indicando que el libro no se puede eliminar debido a asociaciones existentes.
     * Captura cualquier otra excepción genérica, la registra y redirige con un mensaje de error genérico.
     *
     * @param  \App\Models\Libros  $libros Instancia del libro a eliminar (Route Model Binding).
     *                                    Se mantiene el nombre plural `$libros`.
     * @return \Illuminate\Http\RedirectResponse Redirige al índice de libros con mensaje de éxito o error.
     */
    public function destroy(Libros $libros): RedirectResponse
    {
         // 1. Autorización: Verifica rol de administrador.
        if (!Auth::check() || Auth::user()->rol !== 'administrador') {
             abort(403, 'Acción no autorizada.');
        }

        // 2. Intento de Eliminación:
        try {
            // Se llama al método `delete()` sobre la instancia `$libros`.
            $libros->delete();
            // 3. Redirección Éxito:
             return redirect()->route('libros.index')
                ->with('success', 'Libro eliminado correctamente.');

        } catch (\Illuminate\Database\QueryException $e) {
            // 4. Manejo de Error Específico (Restricción de BD):
            // Captura excepciones de consulta de base de datos, que a menudo indican
            // problemas de clave foránea (ej. intentar borrar un libro que existe en `detallespedidos`).
            Log::error("Error de BD al eliminar libro ID {$libros->id}: " . $e->getMessage()); // Loguea el error real.
            // Redirige con un mensaje específico para el usuario.
            return redirect()->route('libros.index')
                ->with('error', 'No se puede eliminar el libro porque está asociado a pedidos existentes.');

        } catch (\Exception $e) {
             // 5. Manejo de Error Genérico:
             // Captura cualquier otra excepción inesperada durante la eliminación.
             Log::error("Error al eliminar libro ID {$libros->id}: " . $e->getMessage()); // Loguea el error.
             // Redirige con un mensaje genérico.
             return redirect()->route('libros.index')
                ->with('error', 'Ocurrió un error al eliminar el libro.');
        }
    }
}
