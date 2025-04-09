<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clientes extends Model
{
    // Definimos la tabla a la que pertenece el modelo
    protected $table = 'clientes';

    // Definimos los campos que se pueden llenar
    protected $fillable = [
        'nombre',
        'apellidos',
        'direccion',
        'telefono',
        'email'
    ];

    // Relación uno a muchos con la tabla pedidos
    public function pedidos()
    {
        return $this->hasMany(Pedidos::class, 'cliente_id');
    }
}
