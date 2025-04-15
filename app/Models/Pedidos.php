<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedidos extends Model
{
    // Definimos la tabla a la que pertenece el modelo
    protected $table = 'pedidos';

    // Definimos los campos que se pueden llenar
    protected $fillable = [
        'cliente_id',
        'fecha_pedido',
        'estado'
    ];

    // Relación con la tabla clientes
    public function cliente()
    {
        return $this->belongsTo(Clientes::class, 'cliente_id');
    }

    // Relación con la tabla empleados
    public function empleado()
    {
        return $this->belongsTo(Empleados::class, 'empleado_id');
    }

    // Relación con la tabla detallespedidos
    public function detallespedidos()
    {
        return $this->hasMany(Detallespedidos::class, 'pedido_id');
    }
}
