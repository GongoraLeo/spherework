<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model; // Clase base de modelos Eloquent.
use App\Models\Libros; // Modelo relacionado para la relación con Libros.
use Illuminate\Database\Eloquent\Factories\HasFactory; // Trait para usar factories.
use Illuminate\Database\Eloquent\Relations\HasMany; // Tipo de relación para el método libros().

/**
 * Class Editoriales
 *
 * Representa una editorial en la base de datos.
 * Este modelo interactúa con la tabla 'editoriales' y define las propiedades
 * que pueden ser asignadas masivamente (`$fillable`) y las relaciones
 * Eloquent con otros modelos, como la relación uno a muchos con `Libros`.
 *
 * @property int $id Identificador único de la editorial.
 * @property string $nombre Nombre de la editorial.
 * @property string $pais País de origen de la editorial.
 * @property \Illuminate\Support\Carbon|null $created_at Fecha y hora de creación.
 * @property \Illuminate\Support\Carbon|null $updated_at Fecha y hora de última actualización.
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Libros[] $libros Los libros publicados por esta editorial.
 *
 * @package App\Models
 */
class Editoriales extends Model
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
     * Especifica explícitamente que este modelo gestiona la tabla `editoriales`.
     *
     * @var string
     */
    protected $table = 'editoriales';

    /**
     * Los atributos que son asignables masivamente.
     *
     * Define una lista blanca de columnas de la tabla `editoriales` que pueden ser
     * llenadas usando los métodos `create` o `fill` / `update`.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre', // El nombre de la editorial.
        'pais',   // El país de origen de la editorial.
    ];

    /**
     * Define la relación "uno a muchos" con el modelo Libros.
     *
     * Establece que una instancia de `Editoriales` puede estar asociada con
     * múltiples instancias de `Libros`. Permite acceder a la colección
     * de libros de la editorial a través de la propiedad `$editorial->libros`.
     * La clave foránea en la tabla `libros` utilizada es `editorial_id`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Retorna el objeto de la relación HasMany.
     */
    public function libros(): HasMany
    {
        // Define que esta editorial tiene muchos ('hasMany') Libros.
        // El segundo argumento 'editorial_id' es la clave foránea en la tabla 'libros'
        // que referencia al 'id' de la tabla 'editoriales'.
        return $this->hasMany(Libros::class, 'editorial_id');
    }
}
