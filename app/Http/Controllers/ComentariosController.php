<?php
// filepath: app/Http/Controllers/ComentariosController.php

namespace App\Http\Controllers;

use App\Models\Comentarios; // Modelo Eloquent para interactuar con la tabla 'comentarios'.
use Illuminate\Http\Request; // Objeto para manejar las solicitudes HTTP entrantes (formularios).
use Illuminate\Support\Facades\Auth; // Fachada para obtener el usuario autenticado y su ID.
use Illuminate\Http\RedirectResponse; // Para especificar el tipo de retorno de redirecciones.
use Illuminate\Support\Facades\Log;    // Fachada para registrar mensajes de error.
use Illuminate\View\View;             // Para especificar el tipo de retorno de vistas.

/**
 * Class ComentariosController
 *
 * Gestiona la creación, edición, actualización y eliminación de comentarios
 * asociados a los libros. Las acciones de edición y eliminación están
 * restringidas al autor del comentario o a un administrador.
 *
 * @package App\Http\Controllers
 */
class ComentariosController extends Controller
{
    // Nota: Los métodos index(), create() y show() no están definidos aquí,
    // ya que la lista de comentarios se muestra generalmente en la vista del libro (libros.show)
    // y la creación se inicia desde allí. La edición se maneja con edit() y update().

    /**
     * Almacena un nuevo comentario (y puntuación) en la base de datos.
     *
     * Este método se llama típicamente desde el formulario en la vista de detalle de un libro (libros.show).
     * Valida los datos recibidos (texto del comentario y puntuación opcional).
     * Asocia el comentario con el libro y el usuario autenticado.
     * Maneja errores durante la creación y redirige de vuelta al libro con un mensaje.
     *
     * @param  \Illuminate\Http\Request  $request Objeto con los datos del formulario (libro_id, texto, puntuacion).
     * @return \Illuminate\Http\RedirectResponse Redirige de vuelta a la página del libro o al formulario si hay error.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validación de Datos: Asegurar que los datos del formulario son válidos.
        // Se usa el método validate() que lanza una excepción si la validación falla.
        $validated = $request->validate([
            'libro_id'   => 'required|exists:libros,id', // El libro debe existir en la tabla 'libros'.
            'texto'      => 'required|string|max:1000', // El comentario es obligatorio, tipo texto, máximo 1000 caracteres.
            'puntuacion' => 'nullable|integer|min:1|max:5', // La puntuación es opcional (nullable), debe ser un entero entre 1 y 5.
        ]);

        // 2. Obtención del Usuario Autenticado: Verificar que el usuario está logueado.
        $userId = Auth::id(); // Obtiene el ID del usuario actualmente autenticado.
        if (!$userId) {
            // Aunque la vista debería prevenir esto, es una capa extra de seguridad.
            // Redirige de vuelta al formulario anterior (`back()`) con error y datos previos (`withInput`).
            return back()->with('error', 'Debes iniciar sesión para comentar.')->withInput();
        }

        // 3. Preparación de Datos para la Creación: Crear un array con los datos a guardar.
        $dataToCreate = [
            'libro_id'   => $validated['libro_id'], // ID del libro validado.
            'user_id'    => $userId, // ID del usuario autenticado.
            'comentario' => $validated['texto'], // Se mapea el campo 'texto' del formulario a la columna 'comentario' de la BD.
            // Se asigna la puntuación validada. Si 'puntuacion' no se envió o estaba vacío,
            // el operador '??' (Null Coalescing) asigna null a la columna 'puntuacion'.
            'puntuacion' => $validated['puntuacion'] ?? null,
        ];

        // 4. Intento de Creación en Base de Datos: Guardar el nuevo comentario.
        try {
            // Se utiliza el método estático `create` del modelo Comentarios.
            // Requiere que los campos en $dataToCreate estén definidos como 'fillable' en el modelo Comentarios.
            Comentarios::create($dataToCreate);
        } catch (\Exception $e) {
            // 5. Manejo de Errores: Si ocurre una excepción durante la creación.
            // Se registra el error en los logs de Laravel para diagnóstico posterior.
            Log::error("Error al crear comentario: " . $e->getMessage(), [
                'user_id' => $userId,
                'libro_id' => $validated['libro_id'],
                'data' => $dataToCreate // Incluye los datos que se intentaron guardar en el log.
            ]);
            // Redirige de vuelta al formulario anterior con mensaje de error y datos previos.
            return back()->with('error', 'Ocurrió un error al guardar tu comentario. Revisa los datos e inténtalo de nuevo.')->withInput();
        }

        // 6. Redirección Éxito: Si el comentario se creó correctamente.
        // Redirige a la vista de detalle del libro (`libros.show`) usando el libro_id validado.
        // Se añade un mensaje flash de éxito a la sesión.
        return redirect()->route('libros.show', $validated['libro_id'])
                       ->with('success', 'Comentario añadido correctamente.');
    }


    /**
     * Muestra el formulario para editar un comentario específico.
     *
     * Utiliza Route Model Binding para obtener la instancia del comentario a editar.
     * Verifica que el usuario autenticado sea el propietario del comentario o un administrador.
     * Carga opcionalmente la relación con el libro para mostrar información en la vista.
     *
     * @param  \App\Models\Comentarios  $comentarios Instancia del modelo Comentarios inyectada por Laravel
     *                                             basada en el parámetro de ruta (ej. /comentarios/{comentario}/edit).
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse Retorna la vista 'comentarios.edit' o redirige si no está autorizado.
     */
    public function edit(Comentarios $comentarios): View|RedirectResponse
    {
        // 1. Autorización: Verificar permisos de edición.
        // El usuario debe ser el dueño del comentario (Auth::id() === $comentarios->user_id)
        // O debe tener el rol de 'administrador'.
        if (Auth::id() !== $comentarios->user_id && Auth::user()->rol !== 'administrador') {
            // Si no cumple ninguna condición, redirige al perfil del usuario con un error.
             return redirect()->route('profile.show')->with('error', 'No tienes permiso para editar este comentario.');
        }

        // 2. Carga Opcional de Relación: Cargar el libro asociado al comentario.
        // `load()` carga la relación si no ha sido cargada previamente. Útil si la vista de edición
        // necesita mostrar, por ejemplo, el título del libro que se está comentando.
        $comentarios->load('libro');

        // 3. Retornar la Vista de Edición:
        // Se renderiza la vista 'resources/views/comentarios/edit.blade.php'.
        // Se pasa la instancia del comentario `$comentarios` (que incluye la relación 'libro' cargada)
        // a la vista usando `compact()`. El formulario usará estos datos para rellenar los campos.
        return view('comentarios.edit', compact('comentarios'));
    }


    /**
     * Actualiza un comentario existente en la base de datos.
     *
     * Utiliza Route Model Binding para obtener la instancia del comentario a actualizar.
     * Verifica la autorización (dueño o administrador).
     * Valida los datos recibidos del formulario de edición (texto y puntuación).
     * Actualiza el registro en la base de datos y maneja posibles errores.
     * Redirige de vuelta a la página del libro con un mensaje.
     *
     * @param  \Illuminate\Http\Request  $request Datos enviados desde el formulario de edición.
     * @param  \App\Models\Comentarios  $comentarios Instancia del comentario a actualizar (Route Model Binding).
     * @return \Illuminate\Http\RedirectResponse Redirige a la vista del libro o de vuelta al formulario si hay error.
     */
     public function update(Request $request, Comentarios $comentarios): RedirectResponse
     {
         // 1. Autorización: Misma lógica que en edit().
         if (Auth::id() !== $comentarios->user_id && Auth::user()->rol !== 'administrador') {
             // Se usa abort(403) para detener la ejecución si no está autorizado.
             abort(403, 'No tienes permiso para editar este comentario.');
         }

         // 2. Validación de Datos: Asegurar que los datos del formulario de edición son válidos.
         $validated = $request->validate([
             'texto'      => 'required|string|max:1000', // Texto del comentario.
             'puntuacion' => 'nullable|integer|min:1|max:5', // Puntuación opcional.
         ]);

         // 3. Preparación de Datos para Actualizar: Crear array con los datos a modificar.
         $dataToUpdate = [
             'comentario' => $validated['texto'], // Mapea 'texto' a 'comentario'.
             'puntuacion' => $validated['puntuacion'] ?? null, // Asigna puntuación o null.
         ];

         // 4. Intento de Actualización en Base de Datos:
         try {
             // Se utiliza el método `update()` sobre la instancia del modelo `$comentarios`.
             // Solo actualizará los campos presentes en $dataToUpdate que sean 'fillable'.
             $comentarios->update($dataToUpdate);
         } catch (\Exception $e) {
             // 5. Manejo de Errores: Si la actualización falla.
             // Registra el error.
             Log::error("Error al actualizar comentario ID {$comentarios->id}: " . $e->getMessage(), [
                 'user_id' => Auth::id(),
                 'data' => $dataToUpdate
             ]);
             // Redirige de vuelta al formulario de edición con error y datos previos.
             return back()->with('error', 'Ocurrió un error al actualizar el comentario.')->withInput();
         }

         // 6. Redirección Éxito: Si la actualización fue correcta.
         // Redirige a la vista de detalle del libro asociado al comentario.
         return redirect()->route('libros.show', $comentarios->libro_id)
                        ->with('success', 'Comentario actualizado correctamente.');
     }


    /**
     * Elimina un comentario específico de la base de datos.
     *
     * Utiliza Route Model Binding para obtener la instancia del comentario a eliminar.
     * Verifica la autorización (dueño o administrador).
     * Elimina el registro y maneja posibles errores.
     * Redirige de vuelta a la página del libro con un mensaje.
     *
     * @param  \App\Models\Comentarios  $comentarios Instancia del comentario a eliminar
     * @return \Illuminate\Http\RedirectResponse Redirige a la vista del libro o de vuelta si hay error.
     */
     public function destroy(Comentarios $comentarios): RedirectResponse
     {
         // Se guarda el ID del libro antes de eliminar el comentario, para la redirección.
         $libroId = $comentarios->libro_id;

         // 1. Autorización: Misma lógica que en edit() y update().
         if (Auth::id() !== $comentarios->user_id && Auth::user()->rol !== 'administrador') {
             abort(403, 'No tienes permiso para eliminar este comentario.');
         }

         // 2. Intento de Eliminación en Base de Datos:
         try {
             // Se llama al método `delete()` sobre la instancia del modelo `$comentarios`.
             $comentarios->delete();
         } catch (\Exception $e) {
             // 3. Manejo de Errores: Si la eliminación falla.
              Log::error("Error al eliminar comentario ID {$comentarios->id}: " . $e->getMessage());
              // Redirige de vuelta a la página anterior (probablemente la del libro) con un error.
              return back()->with('error', 'No se pudo eliminar el comentario.');
         }

         // 4. Redirección Éxito: Si la eliminación fue correcta.
         // Redirige a la vista de detalle del libro del que se eliminó el comentario.
         return redirect()->route('libros.show', $libroId)
                        ->with('success', 'Comentario eliminado correctamente.');
     }
}
