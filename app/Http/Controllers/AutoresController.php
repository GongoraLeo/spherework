<?php
// filepath: app/Http/Controllers/AutoresController.php

namespace App\Http\Controllers;

use App\Models\Autores; // Modelo Eloquent para interactuar con la tabla 'autores'.
use Illuminate\Http\Request; // Objeto para manejar las solicitudes HTTP entrantes.
use Illuminate\Support\Facades\Auth; // Fachada para acceder a los servicios de autenticación (obtener usuario, verificar rol).
use Illuminate\View\View;             // Para especificar el tipo de retorno cuando se devuelve una vista Blade.
use Illuminate\Http\RedirectResponse; // Para especificar el tipo de retorno cuando se realiza una redirección.
use Illuminate\Support\Facades\Log;    // Fachada para registrar mensajes de error o información.
use Illuminate\Validation\Rule;       // Clase para construir reglas de validación más complejas (usada en update).

/**
 * Class AutoresController
 *
 * Controlador encargado de gestionar las operaciones CRUD (Crear, Leer, Actualizar, Eliminar)
 * para el recurso 'Autores', específicamente para el panel de administración.
 * Todas las acciones principales requieren que el usuario autenticado tenga el rol 'administrador'.
 *
 * @package App\Http\Controllers
 */
class AutoresController extends Controller
{
    /**
     * Muestra una lista paginada de todos los autores.
     *
     * Este método está restringido a usuarios administradores.
     * Obtiene todos los autores ordenados por nombre y los pagina.
     * Luego, renderiza la vista 'admin.autores.index' pasándole la colección de autores.
     *
     * @return View|RedirectResponse Retorna la vista del índice de autores o redirige si no es admin.
     */
    public function index(): View|RedirectResponse
    {
        // 1. Autorización: Verificar si el usuario actual es administrador.
        // Se accede al usuario autenticado y se comprueba su atributo 'rol'.
        if (Auth::user()->rol !== 'administrador') {
            // Si no es admin, redirige a la ruta de entrada del perfil con un mensaje flash de error.
            // Se eligió 'profile.entry' como punto centralizado de redirección según rol.
            return redirect()->route('profile.entry')->with('error', 'Acceso no autorizado.');
        }

        // 2. Obtención de datos: Recuperar autores de la base de datos.
        // Se utiliza el modelo Autores, se ordenan por 'nombre' ascendentemente
        // y se pagina el resultado (15 autores por página por defecto).
        // La variable $autores contendrá una instancia de LengthAwarePaginator.
        $autores = Autores::orderBy('nombre')->paginate(15);

        // 3. Retornar la vista: Mostrar la lista.
        // Se renderiza la vista ubicada en 'resources/views/admin/autores/index.blade.php'.
        // Se pasa la colección paginada de autores a la vista usando compact().
        // La vista podrá acceder a los autores a través de la variable $autores.
        return view('admin.autores.index', compact('autores'));
    }

    /**
     * Muestra el formulario para crear un nuevo autor.
     *
     * Restringido a administradores. Simplemente retorna la vista que contiene
     * el formulario HTML para la creación de un nuevo autor.
     *
     * @return View|RedirectResponse Retorna la vista del formulario de creación o redirige si no es admin.
     */
    public function create(): View|RedirectResponse
    {
        // 1. Autorización: Verificar rol de administrador.
        if (Auth::user()->rol !== 'administrador') {
            // Si no tiene permiso, redirige al índice de autores (admin) con un mensaje de error.
            return redirect()->route('admin.autores.index')->with('error', 'No tienes permiso para crear autores.');
        }

        // 2. Retornar la vista del formulario.
        // Apunta a 'resources/views/admin/autores/create.blade.php'.
        return view('admin.autores.create');
    }

    /**
     * Almacena un nuevo autor creado en la base de datos.
     *
     * Restringido a administradores. Valida los datos recibidos del formulario.
     * Si la validación es exitosa, crea un nuevo registro en la tabla 'autores'.
     * Maneja posibles excepciones durante la creación y redirige con mensajes de éxito o error.
     *
     * @param Request $request Objeto que contiene todos los datos enviados en la solicitud HTTP (formulario).
     * @return RedirectResponse Siempre retorna una redirección (al índice si éxito, atrás si error).
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Autorización: Verificar rol de administrador.
        // Se usa abort(403) para detener la ejecución inmediatamente si no es admin,
        // lo cual es adecuado para acciones de modificación de datos.
        if (Auth::user()->rol !== 'administrador') {
            abort(403, 'Acción no autorizada.');
        }

        // 2. Validación: Asegurar que los datos recibidos son correctos.
        // Se definen reglas: 'nombre' es obligatorio, string, máximo 255 caracteres y único en la tabla 'autores'.
        // 'pais' es obligatorio, string, máximo 255 caracteres.
        $request->validate([
            'nombre' => 'required|string|max:255|unique:autores,nombre',
            'pais'   => 'required|string|max:255'
        ]);

        // 3. Creación del Recurso: Intentar guardar el nuevo autor.
        try {
            // Se utiliza el método estático `create` del modelo Autores.
            // Este método espera un array asociativo con los datos. `$request->all()`
            // devuelve todos los datos validados del formulario que coinciden con los
            // atributos fillable del modelo Autores.
            Autores::create($request->all());

            // 4. Redirección Éxito: Si la creación fue exitosa.
            // Redirige a la ruta del índice de autores del admin con un mensaje flash de éxito.
            return redirect()->route('admin.autores.index')
                ->with('success', 'Autor creado correctamente.');

        } catch (\Exception $e) {
            // 5. Manejo de Errores: Si ocurre una excepción durante la creación.
            // Se registra el error detallado en los logs de Laravel para diagnóstico.
            Log::error("Error al crear autor: " . $e->getMessage(), $request->all());
            // Se redirige de vuelta al formulario anterior (`back()`) con un mensaje flash de error
            // y con los datos introducidos por el usuario (`withInput()`) para que no los pierda.
            return back()->with('error', 'Ocurrió un error al crear el autor.')->withInput();
        }
    }

    /**
     * Muestra los detalles de un autor específico.
     *
     * Restringido a administradores. Utiliza Route Model Binding para inyectar
     * automáticamente la instancia del modelo `Autores` correspondiente al ID en la URL.
     *
     * @param Autores $autores Instancia del modelo Autores inyectada automáticamente por Laravel
     *                         basada en el parámetro de ruta (ej. /admin/autores/{autor}).
     *                         Se mantiene el nombre plural `$autores` por consistencia con la solicitud previa.
     * @return View|RedirectResponse Retorna la vista de detalles o redirige si no es admin.
     */
    public function show(Autores $autores): View|RedirectResponse
    {
        // 1. Autorización: Verificar rol de administrador.
        if (Auth::user()->rol !== 'administrador') {
            return redirect()->route('profile.entry')->with('error', 'Acceso no autorizado.');
        }

        // 2. Retornar la vista de detalles.
        // No es necesario buscar el autor con `Autores::find()`, Laravel ya lo ha hecho
        // gracias al Route Model Binding y lo ha inyectado en el parámetro `$autores`.
        // Se pasa la variable `$autores` a la vista 'admin.autores.show' usando `compact()`.
        // La vista accederá a los datos del autor a través de `$autores->nombre`, `$autores->pais`, etc.
        return view('admin.autores.show', compact('autores'));
    }

    /**
     * Muestra el formulario para editar un autor existente.
     *
     * Restringido a administradores. Utiliza Route Model Binding para obtener
     * la instancia del autor a editar.
     *
     * @param Autores $autores Instancia del modelo Autores a editar, inyectada por Laravel.
     *                         Se mantiene el nombre plural `$autores`.
     * @return View|RedirectResponse Retorna la vista del formulario de edición o redirige si no es admin.
     */
    public function edit(Autores $autores): View|RedirectResponse
    {
        // 1. Autorización: Verificar rol de administrador.
        if (Auth::user()->rol !== 'administrador') {
            return redirect()->route('admin.autores.index')->with('error', 'No tienes permiso para editar autores.');
        }

        // 2. Retornar la vista del formulario de edición.
        // Gracias a Route Model Binding, `$autores` ya contiene el autor correcto.
        // Se pasa la variable `$autores` a la vista 'admin.autores.edit'.
        // El formulario en la vista usará los datos de `$autores` para rellenar los campos.
        return view('admin.autores.edit', compact('autores'));
    }

    /**
     * Actualiza un autor existente en la base de datos.
     *
     * Restringido a administradores. Valida los datos recibidos. La regla 'unique'
     * se ajusta para ignorar el ID del autor actual durante la validación.
     * Actualiza el registro y redirige con mensajes de éxito o error.
     *
     * @param Request $request Datos de la solicitud HTTP.
     * @param Autores $autores Instancia del autor a actualizar (Route Model Binding).
     *                         Se mantiene el nombre plural `$autores`.
     * @return RedirectResponse Siempre retorna una redirección.
     */
    public function update(Request $request, Autores $autores): RedirectResponse
    {
        // 1. Autorización: Verificar rol de administrador.
        if (Auth::user()->rol !== 'administrador') {
            abort(403, 'Acción no autorizada.');
        }

        // 2. Validación: Similar a store, pero ajustando la regla 'unique'.
        $request->validate([
            // La regla 'unique:autores,nombre,' . $autores->id asegura que el nombre sea único
            // en la tabla 'autores', columna 'nombre', EXCEPTO para el registro con el ID actual (`$autores->id`).
            'nombre' => 'required|string|max:255|unique:autores,nombre,' . $autores->id,
            'pais'   => 'required|string|max:255'
        ]);

        // 3. Actualización del Recurso: Intentar actualizar el autor.
        try {
            // Se utiliza el método `update()` sobre la instancia del modelo `$autores`
            // inyectada por Route Model Binding. Se le pasa `$request->all()` con los datos validados.
            $autores->update($request->all());

            // 4. Redirección Éxito:
            // Redirige al índice de autores del admin con mensaje de éxito.
            return redirect()->route('admin.autores.index')
                ->with('success', 'Autor actualizado correctamente.');

        } catch (\Exception $e) {
            // 5. Manejo de Errores:
            // Registra el error y redirige de vuelta al formulario de edición con error y datos previos.
            Log::error("Error al actualizar autor ID {$autores->id}: " . $e->getMessage(), $request->all());
            return back()->with('error', 'Ocurrió un error al actualizar el autor.')->withInput();
        }
    }

    /**
     * Elimina un autor específico de la base de datos.
     *
     * Restringido a administradores. Incluye una verificación previa para
     * impedir la eliminación si el autor tiene libros asociados.
     *
     * @param Autores $autores Instancia del autor a eliminar (Route Model Binding).
     *                         Se mantiene el nombre plural `$autores`.
     * @return RedirectResponse Siempre retorna una redirección.
     */
    public function destroy(Autores $autores): RedirectResponse
    {
        // 1. Autorización: Verificar rol de administrador.
        if (Auth::user()->rol !== 'administrador') {
            abort(403, 'Acción no autorizada.');
        }

        // 2. Verificación Previa (Lógica de Negocio): Impedir borrado si tiene dependencias.
        try {
            // Se asume que existe una relación `libros()` definida en el modelo `Autores`.
            // Se cuenta cuántos libros están asociados a este autor.
            // Esta es una medida de seguridad importante para mantener la integridad referencial.
            if ($autores->libros()->count() > 0) {
                 // Si tiene libros, no se elimina y se redirige con un error específico.
                 return redirect()->route('admin.autores.index')
                    ->with('error', 'No se puede eliminar el autor porque tiene libros asociados.');
            }

            // 3. Eliminación del Recurso: Si la verificación pasa.
            // Se llama al método `delete()` sobre la instancia del modelo `$autores`.
            $autores->delete();

            // 4. Redirección Éxito:
            // Redirige al índice con mensaje de éxito.
            return redirect()->route('admin.autores.index')
                ->with('success', 'Autor eliminado correctamente.');

        } catch (\Illuminate\Database\QueryException $e) {
            // 5. Manejo de Errores Específico (Opcional pero recomendado):
            // Captura excepciones de base de datos que podrían ocurrir si, por ejemplo,
            // la relación `libros()` no existe o hay otras restricciones no contempladas.
            Log::error("Error de BD al eliminar autor ID {$autores->id}: " . $e->getMessage());
            return redirect()->route('admin.autores.index')
               ->with('error', 'No se pudo eliminar el autor debido a un problema de base de datos.');
        } catch (\Exception $e) {
            // 6. Manejo de Errores Genérico:
            // Captura cualquier otra excepción inesperada.
            Log::error("Error al eliminar autor ID {$autores->id}: " . $e->getMessage());
            return redirect()->route('admin.autores.index')
                ->with('error', 'Ocurrió un error al eliminar el autor.');
        }
    }
}
