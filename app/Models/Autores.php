<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Trait para usar factories.
use Illuminate\Database\Eloquent\Model; // Clase base de modelos Eloquent.
use Illuminate\Database\Eloquent\Relations\HasMany; // Tipo de relación para el método libros().

/**
 * Class Autores
 *
 * Representa un autor en la base de datos.
 * Este modelo interactúa con la tabla 'autores' y define las propiedades
 * que pueden ser asignadas masivamente (`$fillable`) y las relaciones
 * Eloquent con otros modelos, como la relación uno a muchos con `Libros`.
 *
 * @property int $id Identificador único del autor.
 * @property string $nombre Nombre del autor.
 * @property string $pais País de origen del autor.
 * @property \Illuminate\Support\Carbon|null $created_at Fecha y hora de creación.
 * @property \Illuminate\Support\Carbon|null $updated_at Fecha y hora de última actualización.
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Libros[] $libros Los libros escritos por este autor.
 *
 * @package App\Models
 */
class Autores extends Model
{
    /**
     * Trait HasFactory
     *
     * Habilita la capacidad de usar factories para generar instancias
     * de este modelo, lo cual es útil principalmente para pruebas y seeding.
     */
    use HasFactory;

    /**
     * La tabla asociada con el modelo.
     *
     * Especifica explícitamente que este modelo gestiona la tabla `autores`
     * en la base de datos. Aunque Laravel podría inferirlo por convención,
     * definirlo aquí mejora la claridad.
     *
     * @var string
     */
    protected $table = 'autores';

    /**
     * Los atributos que son asignables masivamente.
     *
     * Define una lista blanca de columnas que pueden ser llenadas usando
     * los métodos `create` o `fill` / `update`. Esto es una medida de seguridad
     * para prevenir la asignación masiva no deseada de otros campos.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre', // El nombre completo del autor.
        'pais',   // El país de origen del autor.
    ];

    /**
     * Define la relación "uno a muchos" con el modelo Libros.
     *
     * Establece que una instancia de `Autores` puede estar asociada con
     * múltiples instancias de `Libros`. Esta función permite acceder
     * a la colección de libros relacionados a través de la propiedad `$autor->libros`.
     * Laravel infiere que la clave foránea en la tabla `libros` es `autor_id`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Retorna el objeto de la relación HasMany.
     */
    public function libros(): HasMany
    {
        // Define que este autor tiene muchos ('hasMany') Libros.
        // El segundo argumento 'autor_id' es la clave foránea en la tabla 'libros'
        // que referencia al 'id' de la tabla 'autores'.
        return $this->hasMany(Libros::class, 'autor_id');
    }

}
