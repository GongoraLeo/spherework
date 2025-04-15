<?php
// app/Models/Comentarios.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Necesario para las relaciones
use App\Models\User;   // Necesario para la relación con User
use App\Models\Libros; // Necesario para la relación con Libros

class Comentarios extends Model
{
    // Definimos la tabla a la que pertenece el modelo
    protected $table = 'comentarios';

    // CORREGIDO: Campos que se pueden llenar
    protected $fillable = [
        'user_id',    // Cambiado de cliente_id
        'libro_id',
        'comentario',
        'puntuacion', // Añadir si quieres guardarla desde el controlador
    ];

    // --- Definir Relaciones ---

    /**
     * Un comentario pertenece a un Usuario (User).
     */
    public function user(): BelongsTo
    {
        // Asegúrate que la clave foránea 'user_id' exista en la tabla comentarios
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Un comentario pertenece a un Libro.
     */
    public function libro(): BelongsTo
    {
        // Asegúrate que la clave foránea 'libro_id' exista en la tabla comentarios
        return $this->belongsTo(Libros::class, 'libro_id');
    }

    // Laravel maneja created_at y updated_at automáticamente.
    // La columna 'fecha' se eliminó en la migración.
}
