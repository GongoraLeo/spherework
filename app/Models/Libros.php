<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model; // Clase base de modelos Eloquent.
use Illuminate\Database\Eloquent\Factories\HasFactory; // Trait para usar factories.
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Tipo de relación para autor() y editorial().
use Illuminate\Database\Eloquent\Relations\HasMany; // Tipo de relación para comentarios() y detallespedidos().
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Tipo de relación para clientes().
use App\Models\Autores; // Modelo relacionado para el autor.
use App\Models\Editoriales; // Modelo relacionado para la editorial.
use App\Models\Comentarios; // Modelo relacionado para los comentarios.
use App\Models\Clientes; // Modelo relacionado para la relación muchos a muchos.
use App\Models\Detallespedidos; // Modelo relacionado para los detalles de pedido.

/**
 * Class Libros
 *
 * Representa un libro en la base de datos.
 * Este modelo interactúa con la tabla 'libros' y define las propiedades
 * asignables masivamente (`$fillable`) y las relaciones Eloquent con otros
 * modelos como `Autores`, `Editoriales`, `Comentarios`, `Clientes` y `Detallespedidos`.
 *
 * @property int $id Identificador único del libro.
 * @property string $titulo Título del libro.
 * @property string $isbn Código ISBN del libro.
 * @property int $anio_publicacion Año de publicación del libro.
 * @property int $autor_id ID del autor del libro (clave foránea).
 * @property int $editorial_id ID de la editorial del libro (clave foránea).
 * @property float $precio Precio del libro.
 * @property \Illuminate\Support\Carbon|null $created_at Fecha y hora de creación.
 * @property \Illuminate\Support\Carbon|null $updated_at Fecha y hora de última actualización.
 *
 * @property-read \App\Models\Autores $autor El autor del libro.
 * @property-read \App\Models\Editoriales $editorial La editorial del libro.
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Comentarios[] $comentarios Los comentarios asociados a este libro.
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Clientes[] $clientes Los clientes asociados a este libro (relación muchos a muchos).
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Detallespedidos[] $detallespedidos Los detalles de pedido que incluyen este libro.
 *
 * @package App\Models
 */
class Libros extends Model
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
     * Especifica explícitamente que este modelo gestiona la tabla `libros`.
     *
     * @var string
     */
    protected $table = 'libros';

    /**
     * Los atributos que son asignables masivamente.
     *
     * Define una lista blanca de columnas de la tabla `libros` que pueden ser
     * llenadas usando los métodos `create` o `fill` / `update`.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'titulo',
        'isbn',
        'anio_publicacion',
        'autor_id',
        'editorial_id',
        'precio'
    ];

    /**
     * Define la relación inversa "pertenece a" con el modelo Autores.
     *
     * Establece que cada instancia de `Libros` está asociada a una única
     * instancia de `Autores`. Permite acceder al autor relacionado a través
     * de la propiedad `$libro->autor`. La clave foránea utilizada es `autor_id`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Retorna el objeto de la relación BelongsTo.
     */
    public function autor(): BelongsTo
    {
        // Define que este libro pertenece a ('belongsTo') un Autor.
        // El segundo argumento 'autor_id' es la clave foránea en la tabla 'libros'.
        return $this->belongsTo(Autores::class, 'autor_id');
    }

    /**
     * Define la relación inversa "pertenece a" con el modelo Editoriales.
     *
     * Establece que cada instancia de `Libros` está asociada a una única
     * instancia de `Editoriales`. Permite acceder a la editorial relacionada a través
     * de la propiedad `$libro->editorial`. La clave foránea utilizada es `editorial_id`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Retorna el objeto de la relación BelongsTo.
     */
    public function editorial(): BelongsTo
    {
        // Define que este libro pertenece a ('belongsTo') una Editorial.
        // El segundo argumento 'editorial_id' es la clave foránea en la tabla 'libros'.
        return $this->belongsTo(Editoriales::class, 'editorial_id');
    }

    /**
     * Define la relación "uno a muchos" con el modelo Comentarios.
     *
     * Establece que una instancia de `Libros` puede tener asociados
     * múltiples instancias de `Comentarios`. Permite acceder a la colección
     * de comentarios del libro a través de la propiedad `$libro->comentarios`.
     * La clave foránea en la tabla `comentarios` utilizada es `libro_id`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Retorna el objeto de la relación HasMany.
     */
    public function comentarios(): HasMany
    {
        // Define que este libro tiene muchos ('hasMany') Comentarios.
        // El segundo argumento 'libro_id' es la clave foránea en la tabla 'comentarios'.
        return $this->hasMany(Comentarios::class, 'libro_id');
    }

    /**
     * Define la relación "muchos a muchos" con el modelo Clientes.
     *
     * Establece una relación muchos a muchos entre `Libros` y `Clientes`
     * a través de la tabla pivote `libros_clientes`. Permite acceder a la
     * colección de clientes asociados a este libro a través de la propiedad
     * `$libro->clientes`. Se especifican explícitamente la tabla pivote
     * y las claves foráneas.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany Retorna el objeto de la relación BelongsToMany.
     */
    public function clientes(): BelongsToMany
    {
        // Define que este libro pertenece a muchos ('belongsToMany') Clientes.
        // 'libros_clientes' es la tabla pivote.
        // 'libro_id' es la clave foránea en la tabla pivote que referencia a este modelo (Libros).
        // 'cliente_id' es la clave foránea en la tabla pivote que referencia al modelo relacionado (Clientes).
        return $this->belongsToMany(Clientes::class, 'libros_clientes', 'libro_id', 'cliente_id');
    }

    /**
     * Define la relación "uno a muchos" con el modelo Detallespedidos.
     *
     * Establece que una instancia de `Libros` puede estar asociada con
     * múltiples instancias de `Detallespedidos` (líneas de pedido). Permite
     * acceder a la colección de detalles de pedido que incluyen este libro
     * a través de la propiedad `$libro->detallespedidos`.
     * La clave foránea en la tabla `detallespedidos` utilizada es `libro_id`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Retorna el objeto de la relación HasMany.
     */
    public function detallespedidos(): HasMany
    {
        // Define que este libro tiene muchos ('hasMany') Detallespedidos.
        // El segundo argumento 'libro_id' es la clave foránea en la tabla 'detallespedidos'.
        return $this->hasMany(Detallespedidos::class, 'libro_id');
    }

}
