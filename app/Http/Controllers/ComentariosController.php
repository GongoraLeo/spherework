<?php
// app/Http/Controllers/ComentariosController.php

namespace App\Http\Controllers;

use App\Models\Comentarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Necesario para obtener el usuario autenticado
use Illuminate\Http\RedirectResponse; // Para el tipo de retorno
use Illuminate\Support\Facades\Log;    // Para loggear errores (opcional)

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
        // 1. Validar los datos que SÍ vienen del formulario
        $validated = $request->validate([
            'libro_id' => 'required|exists:libros,id',
            'texto'    => 'required|string|max:1000', // Validar 'texto'
            // 'puntuacion' => 'nullable|integer|min:1|max:5', // Descomentar si añades puntuación al form
        ]);

        // 2. Obtener el ID del usuario autenticado
        $userId = Auth::id();
        if (!$userId) {
            return back()->with('error', 'Debes iniciar sesión para comentar.')->withInput();
        }

        // 3. Preparar los datos para crear el comentario (usando user_id y comentario)
        $dataToCreate = [
            'libro_id'   => $validated['libro_id'],
            'user_id'    => $userId, // Correcto: usa user_id
            'comentario' => $validated['texto'], // Correcto: mapea 'texto' a 'comentario'
            // 'puntuacion' => $validated['puntuacion'] ?? null, // Asignar si existe
        ];

        // 4. Intentar crear el comentario
        try {
            Comentarios::create($dataToCreate);
        } catch (\Exception $e) {
            Log::error("Error al crear comentario: " . $e->getMessage(), [
                'user_id' => $userId,
                'libro_id' => $validated['libro_id'],
                'data' => $dataToCreate
            ]);
            return back()->with('error', 'Ocurrió un error al guardar tu comentario.')->withInput();
        }

        // 5. Redirigir de vuelta a la página del libro con mensaje de éxito
        return redirect()->route('libros.show', $validated['libro_id'])
                       ->with('success', 'Comentario añadido correctamente.');
    }

     // ... (métodos update y destroy - Asegúrate que usen user_id para autorización) ...

     public function update(Request $request, Comentarios $comentarios): RedirectResponse
     {
         // Añadir autorización
         if (Auth::id() !== $comentarios->user_id && Auth::user()->rol !== 'administrador') { // Usa user_id
             abort(403, 'No tienes permiso para editar este comentario.');
         }
         // ... (resto del update) ...
         $validated = $request->validate(['texto' => 'required|string|max:1000']);
         $comentarios->update(['comentario' => $validated['texto']]);
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
