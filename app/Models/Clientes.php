<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model; // Clase base de modelos Eloquent.
use Illuminate\Database\Eloquent\Factories\HasFactory; // Trait para usar factories.
use Illuminate\Database\Eloquent\Relations\HasMany; // Tipo de relación para el método pedidos().

/**
 * Class Clientes
 *
 * Representa un cliente en la base de datos.
 * Este modelo interactúa con la tabla 'clientes' y define las propiedades
 * que pueden ser asignadas masivamente (`$fillable`) y las relaciones
 * Eloquent con otros modelos, como la relación uno a muchos con `Pedidos`.
 *
 *
 * @property int $id Identificador único del cliente.
 * @property string $nombre Nombre del cliente.
 * @property string $apellidos Apellidos del cliente.
 * @property string $direccion Dirección del cliente.
 * @property string $telefono Número de teléfono del cliente.
 * @property string $email Dirección de correo electrónico del cliente.
 * @property \Illuminate\Support\Carbon|null $created_at Fecha y hora de creación.
 * @property \Illuminate\Support\Carbon|null $updated_at Fecha y hora de última actualización.
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Pedidos[] $pedidos Los pedidos realizados por este cliente.
 *
 * @package App\Models
 */
class Clientes extends Model
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
     * Especifica explícitamente que este modelo gestiona la tabla `clientes`.
     *
     * @var string
     */
    protected $table = 'clientes';

    /**
     * Los atributos que son asignables masivamente.
     *
     * Define una lista blanca de columnas de la tabla `clientes` que pueden ser
     * llenadas usando los métodos `create` o `fill` / `update`.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'apellidos',
        'direccion',
        'telefono',
        'email'
    ];

    /**
     * Define la relación "uno a muchos" con el modelo Pedidos.
     *
     * Establece que una instancia de `Clientes` puede tener asociados
     * múltiples instancias de `Pedidos`. Permite acceder a la colección
     * de pedidos del cliente a través de la propiedad `$cliente->pedidos`.
     * Laravel infiere que la clave foránea en la tabla `pedidos` es `cliente_id`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Retorna el objeto de la relación HasMany.
     */
    public function pedidos(): HasMany
    {
        // Define que este cliente tiene muchos ('hasMany') Pedidos.
        // El segundo argumento 'cliente_id' es la clave foránea en la tabla 'pedidos'
        // que referencia al 'id' de la tabla 'clientes'.
        return $this->hasMany(Pedidos::class, 'cliente_id');
    }
}
