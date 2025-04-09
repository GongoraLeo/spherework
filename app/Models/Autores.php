<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Autores extends Model
{
    //definimos la tabla a la que pertenece el modelo
    protected $table = 'autores';

    // Definimos los campos que se pueden llenar
    protected $fillable = [
        'nombre',
        'apellidos',
        'nacionalidad',
        'fecha_nacimiento',
        'fecha_fallecimiento'
    ];

    // Relación uno a muchos con la tabla libros
    public function libros()
    {
        return $this->hasMany(Libros::class, 'autor_id');
    }
    
}
