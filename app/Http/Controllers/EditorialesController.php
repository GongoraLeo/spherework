<?php
// filepath: app/Http/Controllers/EditorialesController.php

namespace App\Http\Controllers;

use App\Models\Editoriales; // Modelo Eloquent para interactuar con la tabla 'editoriales'.
use Illuminate\Http\Request; // Objeto para manejar las solicitudes HTTP entrantes.
use Illuminate\Support\Facades\Auth; // Fachada para acceder a los servicios de autenticación (obtener usuario, verificar rol).
use Illuminate\View\View;             // Para especificar el tipo de retorno cuando se devuelve una vista Blade.
use Illuminate\Http\RedirectResponse; // Para especificar el tipo de retorno cuando se realiza una redirección.
use Illuminate\Support\Facades\Log;    // Fachada para registrar mensajes de error o información.
use Illuminate\Validation\Rule;       // Clase para construir reglas de validación avanzadas, como 'unique' con excepciones.

/**
 * Class EditorialesController
 *
 * Controlador encargado de gestionar las operaciones CRUD (Crear, Leer, Actualizar, Eliminar)
 * para el recurso 'Editoriales', específicamente dentro del panel de administración.
 * Todas las acciones principales requieren que el usuario autenticado tenga el rol 'administrador'.
 *
 * @package App\Http\Controllers
 */
class EditorialesController extends Controller
{
    /**
     * Muestra una lista paginada de todas las editoriales.
     *
     * Restringido a usuarios administradores. Primero, verifica si el usuario autenticado
     * tiene el rol 'administrador' usando `Auth::user()->rol`. Si no lo es, redirige a la
     * ruta 'profile.entry' con un mensaje de error. Si está autorizado, obtiene las
     * editoriales de la base de datos usando el modelo `Editoriales`, las ordena
     * alfabéticamente por 'nombre' y las pagina (15 por página por defecto) usando `paginate(15)`.
     * Finalmente, renderiza la vista 'admin.editoriales.index' pasándole la colección paginada.
     *
     * @return View|RedirectResponse Retorna la vista 'admin.editoriales.index' o redirige si no es admin.
     */
    public function index(): View|RedirectResponse
    {
        // 1. Autorización: Verifica si el usuario actual es administrador.
        if (Auth::user()->rol !== 'administrador') {
            // Si no es admin, redirige a la ruta de entrada del perfil con un mensaje flash de error.
            return redirect()->route('profile.entry')->with('error', 'Acceso no autorizado.');
        }

        // 2. Obtención de Datos: Recupera editoriales de la base de datos.
        // Se utiliza el modelo Editoriales, se ordenan por 'nombre' ascendentemente
        // y se pagina el resultado (15 editoriales por página por defecto).
        $editoriales = Editoriales::orderBy('nombre')->paginate(15);

        // 3. Retornar la Vista: Muestra la lista.
        // Se renderiza la vista 'resources/views/admin/editoriales/index.blade.php'.
        // Se pasa la colección paginada de editoriales a la vista usando compact().
        // La vista podrá acceder a los datos a través de la variable $editoriales.
        return view('admin.editoriales.index', compact('editoriales'));
    }

    /**
     * Muestra el formulario para crear una nueva editorial.
     *
     * Restringido a administradores. Primero, verifica si el usuario autenticado es administrador.
     * Si no lo es, redirige al índice de editoriales del admin con un mensaje de error.
     * Si está autorizado, simplemente retorna la vista 'admin.editoriales.create' que contiene
     * el formulario HTML para la creación de una nueva editorial.
     *
     * @return View|RedirectResponse Retorna la vista del formulario de creación o redirige si no es admin.
     */
    public function create(): View|RedirectResponse
    {
        // 1. Autorización: Verifica rol de administrador.
        if (Auth::user()->rol !== 'administrador') {
            // Si no tiene permiso, redirige al índice de editoriales (admin) con un mensaje de error.
            return redirect()->route('admin.editoriales.index')->with('error', 'No tienes permiso para crear editoriales.');
        }

        // 2. Retornar la vista del formulario.
        // Apunta a 'resources/views/admin/editoriales/create.blade.php'.
        return view('admin.editoriales.create');
    }

    /**
     * Almacena una nueva editorial creada en la base de datos.
     *
     * Restringido a administradores. Primero, verifica si el usuario es administrador
     * usando `Auth::user()->rol`. Si no lo es, detiene la ejecución con `abort(403)`.
     * Luego, valida los datos recibidos del formulario usando `$request->validate()`.
     * Las reglas especifican que 'nombre' es obligatorio y único en la tabla 'editoriales',
     * y 'pais' es obligatorio. Si la validación falla, Laravel redirige automáticamente.
     * Si la validación es exitosa, intenta crear un nuevo registro en la tabla 'editoriales'
     * usando `Editoriales::create($request->all())` dentro de un bloque try-catch.
     * Si la creación es exitosa, redirige al índice de editoriales del admin ('admin.editoriales.index')
     * con un mensaje de éxito. Si ocurre una excepción durante la creación, registra el error
     * usando `Log::error()` y redirige de vuelta al formulario anterior (`back()`) con un
     * mensaje de error y los datos introducidos (`withInput()`).
     *
     * @param Request $request Objeto que contiene todos los datos enviados en la solicitud HTTP (formulario).
     * @return RedirectResponse Siempre retorna una redirección (al índice si éxito, atrás si error).
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Autorización: Verifica rol de administrador.
        // Se usa abort(403) para detener la ejecución si no está autorizado.
        if (Auth::user()->rol !== 'administrador') {
            abort(403, 'Acción no autorizada.');
        }

        // 2. Validación: Asegura que los datos recibidos son correctos y únicos donde sea necesario.
        $request->validate([
            // 'nombre' es obligatorio, string, máximo 255 caracteres y debe ser único en la tabla 'editoriales'.
            'nombre' => 'required|string|max:255|unique:editoriales,nombre',
            'pais'   => 'required|string|max:255' // 'pais' es obligatorio, string, máximo 255 caracteres.
        ]);

        // 3. Creación del Recurso: Intenta guardar la nueva editorial.
        try {
            // Se utiliza el método estático `create` del modelo Editoriales.
            // `$request->all()` devuelve los datos validados que coinciden con los atributos 'fillable' del modelo.
            Editoriales::create($request->all());

            // 4. Redirección Éxito: Si la creación fue exitosa.
            // Redirige a la ruta del índice de editoriales del admin con un mensaje flash de éxito.
            return redirect()->route('admin.editoriales.index')
                ->with('success', 'Editorial creada correctamente.');

        } catch (\Exception $e) {
            // 5. Manejo de Errores: Si ocurre una excepción durante la creación.
            // Registra el error detallado en los logs.
            Log::error("Error al crear editorial: " . $e->getMessage(), $request->all());
            // Redirige de vuelta al formulario anterior (`back()`) con error y datos previos (`withInput`).
            return back()->with('error', 'Ocurrió un error al crear la editorial.')->withInput();
        }
    }

    /**
     * Muestra los detalles de una editorial específica.
     *
     * Restringido a administradores. Verifica si el usuario autenticado es administrador.
     * Si no lo es, redirige a 'profile.entry'. Si está autorizado, utiliza la instancia
     * del modelo `Editoriales` (`$editoriales`) inyectada automáticamente por Laravel mediante
     * Route Model Binding (basado en el ID de la URL). Pasa esta instancia a la vista
     * 'admin.editoriales.show' usando `compact()`.
     *
     * @param Editoriales $editoriales Instancia del modelo Editoriales inyectada por Laravel
     *                                 basada en el parámetro de ruta (ej. /admin/editoriales/{editorial}).
     *                                 Se mantiene el nombre plural `$editoriales`.
     * @return View|RedirectResponse Retorna la vista de detalles o redirige si no es admin.
     */
    public function show(Editoriales $editoriales): View|RedirectResponse
    {
        // 1. Autorización: Verifica rol de administrador.
        if (Auth::user()->rol !== 'administrador') {
            return redirect()->route('profile.entry')->with('error', 'Acceso no autorizado.');
        }

        // 2. Retornar la vista de detalles.
        // No es necesario buscar la editorial (`Editoriales::find()`), Laravel ya lo hizo (Route Model Binding).
        // Se pasa la variable `$editoriales` a la vista 'admin.editoriales.show'.
        return view('admin.editoriales.show', compact('editoriales'));
    }

    /**
     * Muestra el formulario para editar una editorial existente.
     *
     * Restringido a administradores. Verifica si el usuario es administrador; si no, redirige
     * al índice de editoriales del admin. Si está autorizado, utiliza la instancia del modelo
     * `Editoriales` (`$editoriales`) inyectada por Route Model Binding. Pasa esta instancia a la
     * vista 'admin.editoriales.edit'. El formulario en la vista utilizará los datos del objeto
     * `$editoriales` para rellenar los campos existentes.
     *
     * @param Editoriales $editoriales Instancia del modelo Editoriales a editar, inyectada por Laravel.
     *                                 Se mantiene el nombre plural `$editoriales`.
     * @return View|RedirectResponse Retorna la vista del formulario de edición o redirige si no es admin.
     */
    public function edit(Editoriales $editoriales): View|RedirectResponse
    {
        // 1. Autorización: Verifica rol de administrador.
        if (Auth::user()->rol !== 'administrador') {
            return redirect()->route('admin.editoriales.index')->with('error', 'No tienes permiso para editar editoriales.');
        }

        // 2. Retornar la vista del formulario de edición.
        // `$editoriales` ya contiene la editorial correcta gracias a Route Model Binding.
        // Se pasa la variable `$editoriales` a la vista 'admin.editoriales.edit'.
        // El formulario usará los datos de `$editoriales` para rellenar los campos.
        return view('admin.editoriales.edit', compact('editoriales'));
    }

    /**
     * Actualiza una editorial existente en la base de datos.
     *
     * Restringido a administradores. Verifica la autorización; si falla, usa `abort(403)`.
     * Valida los datos de la solicitud (`$request->validate()`). La regla 'unique' para el
     * campo 'nombre' se ajusta usando `Rule::unique()->ignore($editoriales->id)` para asegurar
     * que el nombre sea único, pero ignorando el registro de la editorial que se está actualizando.
     * Si la validación es correcta, intenta actualizar la editorial usando el método `update()`
     * sobre la instancia `$editoriales` inyectada, pasándole `$request->all()`. Esto ocurre
     * dentro de un bloque try-catch. En caso de éxito, redirige al índice de editoriales del admin
     * con un mensaje de éxito. Si falla, registra el error y redirige de vuelta al formulario
     * de edición con un mensaje de error y los datos introducidos.
     *
     * @param Request $request Datos de la solicitud HTTP (formulario de edición).
     * @param Editoriales $editoriales Instancia de la editorial a actualizar (Route Model Binding).
     *                                 Se mantiene el nombre plural `$editoriales`.
     * @return RedirectResponse Siempre retorna una redirección.
     */
    public function update(Request $request, Editoriales $editoriales): RedirectResponse
    {
        // 1. Autorización: Verifica rol de administrador.
        if (Auth::user()->rol !== 'administrador') {
            abort(403, 'Acción no autorizada.');
        }

        // 2. Validación: Ajustada para la actualización.
        $request->validate([
            'nombre' => [
                'required', // El nombre sigue siendo obligatorio.
                'string',   // Debe ser texto.
                'max:255',  // Límite de caracteres.
                // Regla 'unique' avanzada: verifica unicidad en la tabla 'editoriales', columna 'nombre',
                // pero ignora el registro que tenga el ID de la editorial actual (`$editoriales->id`).
                Rule::unique('editoriales', 'nombre')->ignore($editoriales->id),
            ],
            'pais'   => 'required|string|max:255' // País sigue siendo obligatorio.
        ]);

        // 3. Actualización del Recurso: Intenta actualizar la editorial.
        try {
            // Se utiliza el método `update()` sobre la instancia del modelo `$editoriales`.
            // Se le pasa `$request->all()` con los datos validados.
            $editoriales->update($request->all());

            // 4. Redirección Éxito:
            // Redirige al índice de editoriales del admin con mensaje de éxito.
            return redirect()->route('admin.editoriales.index')
                ->with('success', 'Editorial actualizada correctamente.');

        } catch (\Exception $e) {
            // 5. Manejo de Errores:
            // Registra el error y redirige de vuelta al formulario de edición con error y datos previos.
            Log::error("Error al actualizar editorial ID {$editoriales->id}: " . $e->getMessage(), $request->all());
            return back()->with('error', 'Ocurrió un error al actualizar la editorial.')->withInput();
        }
    }

    /**
     * Elimina una editorial específica de la base de datos.
     *
     * Restringido a administradores. Verifica la autorización; si falla, usa `abort(403)`.
     * Dentro de un bloque try-catch, primero realiza una verificación de lógica de negocio:
     * comprueba si la editorial tiene libros asociados llamando a la relación `libros()` y
     * contando los resultados (`$editoriales->libros()->count() > 0`). Si tiene libros asociados,
     * redirige al índice de editoriales del admin con un mensaje de error específico para prevenir
     * la eliminación y mantener la integridad referencial. Si no tiene libros asociados,
     * procede a eliminar la editorial usando `$editoriales->delete()`. Si la eliminación es exitosa,
     * redirige al índice con un mensaje de éxito. Si ocurre una `QueryException` (por ejemplo,
     * por otras restricciones de base de datos), se captura específicamente, se registra el error
     * y se redirige con un mensaje de error de BD. Cualquier otra excepción genérica también
     * se captura, se registra y se redirige con un mensaje de error genérico.
     *
     * @param Editoriales $editoriales Instancia de la editorial a eliminar (Route Model Binding).
     *                                 Se mantiene el nombre plural `$editoriales`.
     * @return RedirectResponse Siempre retorna una redirección.
     */
    public function destroy(Editoriales $editoriales): RedirectResponse
    {
        // 1. Autorización: Verifica rol de administrador.
        if (Auth::user()->rol !== 'administrador') {
            abort(403, 'Acción no autorizada.');
        }

        // 2. Verificación Previa (Lógica de Negocio): Impedir borrado si tiene dependencias.
        try {
            // Se asume que existe una relación `libros()` definida en el modelo `Editoriales`.
            // Se cuenta cuántos libros están asociados a esta editorial.
            if ($editoriales->libros()->count() > 0) {
                 // Si tiene libros asociados, no se elimina y se redirige con un error específico.
                 return redirect()->route('admin.editoriales.index')
                    ->with('error', 'No se puede eliminar la editorial porque tiene libros asociados.');
            }

            // 3. Eliminación del Recurso: Si la verificación pasa.
            // Se llama al método `delete()` sobre la instancia del modelo `$editoriales`.
            $editoriales->delete();

            // 4. Redirección Éxito:
            // Redirige al índice con mensaje de éxito.
            return redirect()->route('admin.editoriales.index')
                ->with('success', 'Editorial eliminada correctamente.');

        } catch (\Illuminate\Database\QueryException $e) {
             // 5. Manejo de Errores Específico (Base de Datos):
             // Captura excepciones de BD, por ejemplo, si la relación `libros()` no existe
             // o hay otras restricciones de clave foránea no contempladas.
             Log::error("Error de BD al eliminar editorial ID {$editoriales->id}: " . $e->getMessage());
             return redirect()->route('admin.editoriales.index')
                ->with('error', 'No se pudo eliminar la editorial debido a restricciones de base de datos.');
        } catch (\Exception $e) {
            // 6. Manejo de Errores Genérico:
            // Captura cualquier otra excepción inesperada.
            Log::error("Error al eliminar editorial ID {$editoriales->id}: " . $e->getMessage());
            return redirect()->route('admin.editoriales.index')
                ->with('error', 'Ocurrió un error al eliminar la editorial.');
        }
    }
}
