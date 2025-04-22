<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Trait para usar factories.
use Illuminate\Database\Eloquent\Model; // Clase base de modelos Eloquent.
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Tipo de relación para pedido() y libro().
use App\Models\Pedidos; // Modelo relacionado para el pedido al que pertenece el detalle.
use App\Models\Libros; // Modelo relacionado para el libro incluido en el detalle.

/**
 * Class Detallespedidos
 *
 * Representa un ítem individual dentro de un pedido (o carrito de compras pendiente).
 * Cada instancia de este modelo corresponde a una línea en la tabla `detallespedidos`,
 * vinculando un pedido específico con un libro específico, e indicando la cantidad
 * y el precio unitario en el momento de la transacción o adición al carrito.
 *
 * @property int $id Identificador único del detalle del pedido.
 * @property int $pedido_id ID del pedido al que pertenece este detalle.
 * @property int $libro_id ID del libro incluido en este detalle.
 * @property int $cantidad Número de unidades de este libro en el detalle.
 * @property float $precio Precio unitario del libro en el momento de añadirlo/comprarlo.
 * @property \Illuminate\Support\Carbon|null $created_at Fecha y hora de creación.
 * @property \Illuminate\Support\Carbon|null $updated_at Fecha y hora de última actualización.
 * @property-read \App\Models\Pedidos $pedido El pedido al que pertenece este detalle.
 * @property-read \App\Models\Libros $libro El libro asociado a este detalle.
 *
 * @package App\Models
 */
class Detallespedidos extends Model
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
     * Especifica explícitamente que este modelo gestiona la tabla `detallespedidos`.
     *
     * @var string
     */
    protected $table = 'detallespedidos';

    /**
     * Los atributos que son asignables masivamente.
     *
     * Define una lista blanca de columnas de la tabla `detallespedidos` que pueden ser
     * llenadas usando los métodos `create` o `fill` / `update`. Incluye las claves
     * foráneas, la cantidad y el precio, ya que estos son establecidos por el
     * `DetallespedidosController` al añadir o modificar ítems en el carrito.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pedido_id', // Clave foránea que referencia al pedido (tabla 'pedidos').
        'libro_id',  // Clave foránea que referencia al libro (tabla 'libros').
        'cantidad',  // Número de unidades del libro.
        'precio',    // Precio unitario del libro en el momento de la operación.
    ];

    /**
     * Define la relación inversa "pertenece a" con el modelo Pedidos.
     *
     * Establece que cada instancia de `Detallespedidos` está asociada a una única
     * instancia de `Pedidos`. Permite acceder al pedido relacionado a través
     * de la propiedad `$detalle->pedido`. La clave foránea utilizada es `pedido_id`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Retorna el objeto de la relación BelongsTo.
     */
    public function pedido(): BelongsTo
    {
        // Define que este detalle pertenece a ('belongsTo') un Pedido.
        // El segundo argumento 'pedido_id' es la clave foránea en la tabla 'detallespedidos'.
        return $this->belongsTo(Pedidos::class, 'pedido_id');
    }

    /**
     * Define la relación inversa "pertenece a" con el modelo Libros.
     *
     * Establece que cada instancia de `Detallespedidos` está asociada a una única
     * instancia de `Libros`. Permite acceder al libro relacionado a través
     * de la propiedad `$detalle->libro`. La clave foránea utilizada es `libro_id`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Retorna el objeto de la relación BelongsTo.
     */
    public function libro(): BelongsTo
    {
        // Define que este detalle pertenece a ('belongsTo') un Libro.
        // El segundo argumento 'libro_id' es la clave foránea en la tabla 'detallespedidos'.
        return $this->belongsTo(Libros::class, 'libro_id');
    }
}
