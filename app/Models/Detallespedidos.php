<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Detallespedidos extends Model
{
    use HasFactory;

    // Especifica la tabla si no sigue la convención 'detallespedidos'
    protected $table = 'detallespedidos';

    // Define los campos que se pueden asignar masivamente (basado en tu controller)
    protected $fillable = [
        'pedido_id',
        'libro_id',
        'cantidad',
        'precio', // Incluimos precio aquí porque tu controller lo valida y crea
    ];

    /**
     * Define la relación inversa con Pedidos.
     * Un detalle pertenece a un pedido.
     */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedidos::class, 'pedido_id');
    }

    /**
     * Define la relación inversa con Libros.
     * Un detalle pertenece a un libro.
     */
    public function libro(): BelongsTo
    {
        return $this->belongsTo(Libros::class, 'libro_id');
    }

    // Laravel maneja created_at y updated_at por defecto si existen en la tabla.
    // Si no existen, añade: public $timestamps = false;
}
