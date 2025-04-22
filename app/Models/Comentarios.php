<?php
// app/Models/Comentarios.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model; // Clase base de modelos Eloquent.
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Tipo de relación para user() y libro().
use App\Models\User;   // Modelo relacionado para el autor del comentario.
use App\Models\Libros; // Modelo relacionado para el libro comentado.
use Illuminate\Database\Eloquent\Factories\HasFactory; // Trait para usar factories.

/**
 * Class Comentarios
 *
 * Representa un comentario (y opcionalmente una puntuación) realizado por un usuario
 * sobre un libro específico.
 * Este modelo interactúa con la tabla 'comentarios' y define las propiedades
 * asignables masivamente (`$fillable`) y las relaciones inversas ('belongsTo')
 * con los modelos `User` y `Libros`.
 *
 * @property int $id Identificador único del comentario.
 * @property int $user_id ID del usuario que realizó el comentario.
 * @property int $libro_id ID del libro que fue comentado.
 * @property string $comentario El texto del comentario.
 * @property int|null $puntuacion La puntuación numérica (ej. 1-5) asociada al comentario, si existe.
 * @property \Illuminate\Support\Carbon|null $created_at Fecha y hora de creación.
 * @property \Illuminate\Support\Carbon|null $updated_at Fecha y hora de última actualización.
 * @property-read \App\Models\User $user El usuario que escribió el comentario.
 * @property-read \App\Models\Libros $libro El libro al que pertenece el comentario.
 *
 * @package App\Models
 */
class Comentarios extends Model
{
    /**
     * Trait HasFactory
     *
     * Habilita la capacidad de usar factories para generar instancias
     * de este modelo, útil para pruebas y seeding.
     */
    use HasFactory;

    /**
     * La tabla asociada con el modelo.
     *
     * Especifica explícitamente que este modelo gestiona la tabla `comentarios`.
     *
     * @var string
     */
    protected $table = 'comentarios';

    /**
     * Los atributos que son asignables masivamente.
     *
     * Define una lista blanca de columnas de la tabla `comentarios` que pueden ser
     * llenadas usando los métodos `create` o `fill` / `update`. Se corrigió para
     * usar `user_id` en lugar de `cliente_id` y se incluyó `puntuacion`.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',    // Clave foránea que referencia al usuario (tabla 'users').
        'libro_id',   // Clave foránea que referencia al libro (tabla 'libros').
        'comentario', // El contenido textual del comentario.
        'puntuacion', // La valoración numérica opcional dada por el usuario.
    ];

    // --- Definir Relaciones ---

    /**
     * Define la relación inversa "pertenece a" con el modelo User.
     *
     * Establece que cada instancia de `Comentarios` está asociada a una única
     * instancia de `User` (el autor del comentario). Permite acceder al usuario
     * relacionado a través de la propiedad `$comentario->user`.
     * La clave foránea utilizada es `user_id`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Retorna el objeto de la relación BelongsTo.
     */
    public function user(): BelongsTo
    {
        // Define que este comentario pertenece a ('belongsTo') un User.
        // El segundo argumento 'user_id' es la clave foránea en la tabla 'comentarios'.
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Define la relación inversa "pertenece a" con el modelo Libros.
     *
     * Establece que cada instancia de `Comentarios` está asociada a una única
     * instancia de `Libros` (el libro comentado). Permite acceder al libro
     * relacionado a través de la propiedad `$comentario->libro`.
     * La clave foránea utilizada es `libro_id`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Retorna el objeto de la relación BelongsTo.
     */
    public function libro(): BelongsTo
    {
        // Define que este comentario pertenece a ('belongsTo') un Libro.
        // El segundo argumento 'libro_id' es la clave foránea en la tabla 'comentarios'.
        return $this->belongsTo(Libros::class, 'libro_id');
    }
}
