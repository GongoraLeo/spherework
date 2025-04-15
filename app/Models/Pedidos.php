<?php
// app/Models/Pedidos.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User; // Asegúrate que esté

class Pedidos extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    // --- Constantes para la columna 'status' ---
    const STATUS_PENDIENTE = 'pendiente';
    const STATUS_PROCESANDO = 'procesando';
    const STATUS_COMPLETADO = 'completado';
    const STATUS_ENVIADO = 'enviado';
    const STATUS_ENTREGADO = 'entregado';
    const STATUS_CANCELADO = 'cancelado';
    // --- ---

    // CORREGIDO: Eliminado 'fecha' de fillable
    protected $fillable = [
        'cliente_id',
        'fecha_pedido', // Mantener
        'status',       // Mantener
        'total',        // Mantener
        // 'fecha',     // ELIMINADO
    ];

    // Relación con User (corregida)
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    // Relación con Detallespedidos
    public function detallespedido(): HasMany // Mantenemos nombre singular por consistencia con código anterior
    {
        return $this->hasMany(Detallespedidos::class, 'pedido_id');
    }

    // CORREGIDO: Eliminado 'fecha' de casts
    protected $casts = [
        'fecha_pedido' => 'datetime', // Mantener
        // 'fecha' => 'date',         // ELIMINADO
    ];
}

