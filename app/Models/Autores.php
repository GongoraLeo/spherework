<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Autores extends Model
{
    use HasFactory;
    //definimos la tabla a la que pertenece el modelo
    protected $table = 'autores';

    // Definimos los campos que se pueden llenar
    protected $fillable = [
        'nombre',
        'pais',
    ];

    // Relación uno a muchos con la tabla libros
    public function libros()
    {
        return $this->hasMany(Libros::class, 'autor_id');
    }
    
}
