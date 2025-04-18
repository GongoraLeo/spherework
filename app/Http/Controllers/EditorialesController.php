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
     * Restringido a usuarios administradores. Obtiene las editoriales ordenadas
     * por nombre y las pagina para una visualización eficiente.
     * Renderiza la vista del índice de editoriales del panel de administración.
     *
     * @return View|RedirectResponse Retorna la vista 'admin.editoriales.index' o redirige si no es admin.
     */
    public function index(): View|RedirectResponse
    {
        // 1. Autorización: Verificar si el usuario actual es administrador.
        if (Auth::user()->rol !== 'administrador') {
            // Si no es admin, redirige a la ruta de entrada del perfil con un mensaje flash de error.
            return redirect()->route('profile.entry')->with('error', 'Acceso no autorizado.');
        }

        // 2. Obtención de Datos: Recuperar editoriales de la base de datos.
        // Se utiliza el modelo Editoriales, se ordenan por 'nombre' ascendentemente
        // y se pagina el resultado (15 editoriales por página por defecto).
        $editoriales = Editoriales::orderBy('nombre')->paginate(15);

        // 3. Retornar la Vista: Mostrar la lista.
        // Se renderiza la vista 'resources/views/admin/editoriales/index.blade.php'.
        // Se pasa la colección paginada de editoriales a la vista usando compact().
        // La vista podrá acceder a los datos a través de la variable $editoriales.
        return view('admin.editoriales.index', compact('editoriales'));
    }

    /**
     * Muestra el formulario para crear una nueva editorial.
     *
     * Restringido a administradores. Simplemente retorna la vista que contiene
     * el formulario HTML para la creación de una nueva editorial.
     *
     * @return View|RedirectResponse Retorna la vista del formulario de creación o redirige si no es admin.
     */
    public function create(): View|RedirectResponse
    {
        // 1. Autorización: Verificar rol de administrador.
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
     * Restringido a administradores. Valida los datos recibidos del formulario
     * (nombre único y país). Si la validación es exitosa, crea un nuevo registro
     * en la tabla 'editoriales'. Maneja posibles excepciones durante la creación
     * y redirige con mensajes de éxito o error.
     *
     * @param Request $request Objeto que contiene todos los datos enviados en la solicitud HTTP (formulario).
     * @return RedirectResponse Siempre retorna una redirección (al índice si éxito, atrás si error).
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Autorización: Verificar rol de administrador.
        // Se usa abort(403) para detener la ejecución si no está autorizado.
        if (Auth::user()->rol !== 'administrador') {
            abort(403, 'Acción no autorizada.');
        }

        // 2. Validación: Asegurar que los datos recibidos son correctos y únicos donde sea necesario.
        $request->validate([
            // 'nombre' es obligatorio, string, máximo 255 caracteres y debe ser único en la tabla 'editoriales'.
            'nombre' => 'required|string|max:255|unique:editoriales,nombre',
            'pais'   => 'required|string|max:255' // 'pais' es obligatorio, string, máximo 255 caracteres.
        ]);

        // 3. Creación del Recurso: Intentar guardar la nueva editorial.
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
     * Restringido a administradores. Utiliza Route Model Binding para inyectar
     * automáticamente la instancia del modelo `Editoriales` correspondiente al ID en la URL.
     *
     * @param Editoriales $editoriales Instancia del modelo Editoriales inyectada por Laravel
     *                                 basada en el parámetro de ruta (ej. /admin/editoriales/{editorial}).
     *                                 Se mantiene el nombre plural `$editoriales`.
     * @return View|RedirectResponse Retorna la vista de detalles o redirige si no es admin.
     */
    public function show(Editoriales $editoriales): View|RedirectResponse
    {
        // 1. Autorización: Verificar rol de administrador.
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
     * Restringido a administradores. Utiliza Route Model Binding para obtener
     * la instancia de la editorial a editar.
     *
     * @param Editoriales $editoriales Instancia del modelo Editoriales a editar, inyectada por Laravel.
     *                                 Se mantiene el nombre plural `$editoriales`.
     * @return View|RedirectResponse Retorna la vista del formulario de edición o redirige si no es admin.
     */
    public function edit(Editoriales $editoriales): View|RedirectResponse
    {
        // 1. Autorización: Verificar rol de administrador.
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
     * Restringido a administradores. Valida los datos recibidos, asegurando que el
     * nombre sea único excepto para la editorial actual que se está editando.
     * Actualiza el registro y redirige con mensajes de éxito o error.
     *
     * @param Request $request Datos de la solicitud HTTP (formulario de edición).
     * @param Editoriales $editoriales Instancia de la editorial a actualizar (Route Model Binding).
     *                                 Se mantiene el nombre plural `$editoriales`.
     * @return RedirectResponse Siempre retorna una redirección.
     */
    public function update(Request $request, Editoriales $editoriales): RedirectResponse
    {
        // 1. Autorización: Verificar rol de administrador.
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

        // 3. Actualización del Recurso: Intentar actualizar la editorial.
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
     * Restringido a administradores. Incluye una verificación previa para
     * impedir la eliminación si la editorial tiene libros asociados, manteniendo
     * la integridad referencial.
     *
     * @param Editoriales $editoriales Instancia de la editorial a eliminar (Route Model Binding).
     *                                 Se mantiene el nombre plural `$editoriales`.
     * @return RedirectResponse Siempre retorna una redirección.
     */
    public function destroy(Editoriales $editoriales): RedirectResponse
    {
        // 1. Autorización: Verificar rol de administrador.
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
