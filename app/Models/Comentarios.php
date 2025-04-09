<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comentarios extends Model
{
    // Definimos la tabla a la que pertenece el modelo
    protected $table = 'comentarios';

    // Definimos los campos que se pueden llenar
    protected $fillable = [
        'cliente_id',
        'libro_id',
        'comentario'
    ];

    
}
