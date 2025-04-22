<?php
// app/Models/Pedidos.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Trait para usar factories.
use Illuminate\Database\Eloquent\Model; // Clase base de modelos Eloquent.
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Tipo de relación para cliente().
use Illuminate\Database\Eloquent\Relations\HasMany; // Tipo de relación para detallespedidos().
use App\Models\User; // Modelo relacionado para el cliente.
use App\Models\Detallespedidos; // Modelo relacionado para los detalles del pedido.

/**
 * Class Pedidos
 *
 * Representa un pedido realizado por un usuario en la aplicación.
 * Este modelo interactúa con la tabla 'pedidos' y define las propiedades
 * asignables masivamente (`$fillable`), los tipos de datos (`$casts`),
 * constantes para los estados del pedido, y las relaciones Eloquent con
 * los modelos `User` (cliente) y `Detallespedidos` (ítems del pedido).
 *
 * @property int $id Identificador único del pedido.
 * @property int $cliente_id ID del usuario (cliente) que realizó el pedido.
 * @property \Illuminate\Support\Carbon|null $fecha_pedido Fecha y hora en que se realizó o completó el pedido.
 * @property string $status Estado actual del pedido (ej. 'pendiente', 'completado').
 * @property float|null $total Costo total del pedido.
 * @property \Illuminate\Support\Carbon|null $created_at Fecha y hora de creación del registro.
 * @property \Illuminate\Support\Carbon|null $updated_at Fecha y hora de última actualización del registro.
 *
 * @property-read \App\Models\User $cliente El usuario (cliente) al que pertenece el pedido.
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Detallespedidos[] $detallespedidos Los detalles (ítems) asociados a este pedido.
 *
 * @package App\Models
 */
class Pedidos extends Model
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
     * Especifica explícitamente que este modelo gestiona la tabla `pedidos`.
     *
     * @var string
     */
    protected $table = 'pedidos';

    // --- Constantes para la columna 'status' ---
    /** @var string Estado inicial del pedido, representa el carrito antes del checkout. */
    const STATUS_PENDIENTE = 'pendiente';
    /** @var string Estado que indica que el pedido está siendo preparado. */
    const STATUS_PROCESANDO = 'procesando';
    /** @var string Estado que indica que el pedido ha sido finalizado y pagado (o confirmado). */
    const STATUS_COMPLETADO = 'completado';
    /** @var string Estado que indica que el pedido ha sido despachado para entrega. */
    const STATUS_ENVIADO = 'enviado';
    /** @var string Estado que indica que el pedido ha sido recibido por el cliente. */
    const STATUS_ENTREGADO = 'entregado';
    /** @var string Estado que indica que el pedido ha sido cancelado. */
    const STATUS_CANCELADO = 'cancelado';
    // --- ---

    /**
     * Los atributos que son asignables masivamente.
     *
     * Define una lista blanca de columnas de la tabla `pedidos` que pueden ser
     * llenadas usando los métodos `create` o `fill` / `update`.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cliente_id',   // ID del usuario asociado.
        'fecha_pedido', // Fecha y hora del pedido.
        'status',       // Estado actual del pedido.
        'total',        // Costo total calculado.
    ];

    /**
     * Define la relación inversa "pertenece a" con el modelo User.
     *
     * Establece que cada instancia de `Pedidos` está asociada a una única
     * instancia de `User` (el cliente). Permite acceder al cliente relacionado
     * a través de la propiedad `$pedido->cliente`. La clave foránea utilizada
     * es `cliente_id`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Retorna el objeto de la relación BelongsTo.
     */
    public function cliente(): BelongsTo
    {
        // Define que este pedido pertenece a ('belongsTo') un User.
        // El segundo argumento 'cliente_id' es la clave foránea en la tabla 'pedidos'.
        return $this->belongsTo(User::class, 'cliente_id');
    }

    /**
     * Define la relación "uno a muchos" con el modelo Detallespedidos.
     *
     * Establece que una instancia de `Pedidos` puede tener asociados
     * múltiples instancias de `Detallespedidos` (los ítems del pedido). Permite
     * acceder a la colección de detalles del pedido a través de la propiedad
     * `$pedido->detallespedidos`. La clave foránea en la tabla `detallespedidos`
     * utilizada es `pedido_id`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Retorna el objeto de la relación HasMany.
     */
    public function detallespedidos(): HasMany
    {
        // Define que este pedido tiene muchos ('hasMany') Detallespedidos.
        // El segundo argumento 'pedido_id' es la clave foránea en la tabla 'detallespedidos'.
        return $this->hasMany(Detallespedidos::class, 'pedido_id');
    }

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * Especifica que el atributo `fecha_pedido` debe ser tratado como
     * una instancia de Carbon (objeto de fecha y hora).
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_pedido' => 'datetime', // Convierte 'fecha_pedido' a objeto Carbon.
    ];
}
