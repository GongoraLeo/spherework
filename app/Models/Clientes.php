<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Clientes extends Model
{
    use HasFactory;
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
