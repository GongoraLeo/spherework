<?php
// app/Http/Controllers/ComentariosController.php

namespace App\Http\Controllers;

use App\Models\Comentarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Necesario para obtener el usuario autenticado
use Illuminate\Http\RedirectResponse; // Para el tipo de retorno
use Illuminate\Support\Facades\Log;    // Para loggear errores (opcional)
use Illuminate\View\View;             // Para el tipo de retorno



class ComentariosController extends Controller
{
    // ... (métodos index, create, show, edit - sin cambios respecto a la versión anterior) ...

    /**
     * Store a newly created resource in storage.
     * (Guarda el comentario enviado desde libros.show)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    
     public function store(Request $request): RedirectResponse
    {
        // 1. Validar los datos (incluyendo la nueva puntuación)
        $validated = $request->validate([
            'libro_id'   => 'required|exists:libros,id',
            'texto'      => 'required|string|max:1000', // El campo del textarea
            // ***** MODIFICADO AQUÍ: Añadir validación para puntuacion *****
            'puntuacion' => 'nullable|integer|min:1|max:5', // Opcional, entero entre 1 y 5
            // ***** FIN MODIFICACIÓN *****
        ]);

        // 2. Obtener el ID del usuario autenticado
        $userId = Auth::id();
        if (!$userId) {
            // Aunque el formulario solo se muestra a usuarios logueados, es buena práctica verificar
            return back()->with('error', 'Debes iniciar sesión para comentar.')->withInput();
        }

        // 3. Preparar los datos para crear el comentario
        $dataToCreate = [
            'libro_id'   => $validated['libro_id'],
            'user_id'    => $userId,
            'comentario' => $validated['texto'], // Mapea 'texto' del form a 'comentario' de la BD
            // ***** MODIFICADO AQUÍ: Asignar puntuacion si existe *****
            'puntuacion' => $validated['puntuacion'] ?? null, // Asigna el valor validado o null si no se envió
            // ***** FIN MODIFICACIÓN *****
        ];

        // 4. Intentar crear el comentario
        try {
            Comentarios::create($dataToCreate);
        } catch (\Exception $e) {
            Log::error("Error al crear comentario: " . $e->getMessage(), [
                'user_id' => $userId,
                'libro_id' => $validated['libro_id'],
                'data' => $dataToCreate // Loguear los datos que se intentaron guardar
            ]);
            // Considera añadir $e->getTraceAsString() para más detalles en el log si es necesario
            return back()->with('error', 'Ocurrió un error al guardar tu comentario. Revisa los datos e inténtalo de nuevo.')->withInput();
        }

        // 5. Redirigir de vuelta a la página del libro con mensaje de éxito
        return redirect()->route('libros.show', $validated['libro_id'])
                       ->with('success', 'Comentario añadido correctamente.');
    }

    

    /**
     * Show the form for editing the specified resource.
     * Muestra el formulario para editar un comentario específico.
     *
     * @param  \App\Models\Comentarios  $comentarios // Route model binding
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(Comentarios $comentarios): View|RedirectResponse // Usa el nombre del parámetro de la ruta
    {
        // 1. Autorización: Asegurarse que el usuario actual es el dueño del comentario o es admin
        if (Auth::id() !== $comentarios->user_id && Auth::user()->rol !== 'administrador') {
            // Si no tiene permiso, redirigir o abortar
             return redirect()->route('profile.show')->with('error', 'No tienes permiso para editar este comentario.');
            // Opcionalmente: abort(403, 'No tienes permiso para editar este comentario.');
        }

        // 2. Cargar relación con el libro (opcional, para mostrar título en la vista edit)
        $comentarios->load('libro');

        // 3. Retornar la vista de edición, pasando el comentario
        return view('comentarios.edit', compact('comentarios')); // Pasa la variable con el mismo nombre
    }


    /**
     * Update the specified resource in storage.
     * (Ya modificado para incluir puntuacion)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Comentarios  $comentarios // Route model binding
     * @return \Illuminate\Http\RedirectResponse
     */

     public function update(Request $request, Comentarios $comentarios): RedirectResponse
     {
         // 1. Autorización (sin cambios)
         if (Auth::id() !== $comentarios->user_id && Auth::user()->rol !== 'administrador') {
             abort(403, 'No tienes permiso para editar este comentario.');
         }

         // 2. Validar los datos (incluyendo puntuacion)
         //    Asegúrate que el formulario de edición envíe 'texto' y 'puntuacion'
         $validated = $request->validate([
             'texto'      => 'required|string|max:1000',
             'puntuacion' => 'nullable|integer|min:1|max:5', // Validar puntuación
         ]);

         // 3. Preparar los datos para actualizar
         $dataToUpdate = [
             'comentario' => $validated['texto'],
             'puntuacion' => $validated['puntuacion'] ?? null, // Asigna valor o null
         ];

         // 4. Actualizar el comentario en la base de datos
         try {
             $comentarios->update($dataToUpdate);
         } catch (\Exception $e) {
             Log::error("Error al actualizar comentario ID {$comentarios->id}: " . $e->getMessage(), [
                 'user_id' => Auth::id(),
                 'data' => $dataToUpdate
             ]);
             // Redirigir con error si falla la actualización
             return back()->with('error', 'Ocurrió un error al actualizar el comentario.')->withInput();
         }


         // 5. Redirigir de vuelta a la página del libro con mensaje de éxito
         return redirect()->route('libros.show', $comentarios->libro_id)
                        ->with('success', 'Comentario actualizado correctamente.');
     }



     public function destroy(Comentarios $comentarios): RedirectResponse
     {
         $libroId = $comentarios->libro_id;
         // Añadir autorización
         if (Auth::id() !== $comentarios->user_id && Auth::user()->rol !== 'administrador') { // Usa user_id
             abort(403, 'No tienes permiso para eliminar este comentario.');
         }
         // ... (resto del destroy) ...
         try {
             $comentarios->delete();
         } catch (\Exception $e) {
              Log::error("Error al eliminar comentario ID {$comentarios->id}: " . $e->getMessage());
              return back()->with('error', 'No se pudo eliminar el comentario.');
         }
         return redirect()->route('libros.show', $libroId)
                        ->with('success', 'Comentario eliminado correctamente.');
     }
}
