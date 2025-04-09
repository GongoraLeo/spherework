<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Autores;
use App\Models\Editoriales;

class Libros extends Model
{
    use HasFactory;
    // Definimos la tabla a la que pertenece el modelo
    protected $table = 'libros';

    // Definimos los campos que se pueden llenar
    protected $fillable = [
        'titulo',
        'isbn',
        'anio_publicacion',
        'autor_id',
        'editorial_id'
    ];

    //debe tener una relacion uno a muchos con la tabla autores y otra relacion en este caso uno a uno con la tabla editoriales
    public function autor(): BelongsTo
    {
        return $this->belongsTo(Autores::class, 'autor_id');
    }
    public function editorial(): HasOne
    {
        return $this->hasOne(Editoriales::class, 'editorial_id');
    }

    //relacion uno a muchos con la tabla comentarios
    public function comentarios()
    {
        return $this->hasMany(Comentarios::class, 'libro_id');
    }
    //relacion muchos a muchos con la tabla clientes
    public function clientes()
    {
        return $this->belongsToMany(Clientes::class, 'libros_clientes', 'libro_id', 'cliente_id');
    }
   
    //relacion uno a muchos con la tabla detallespedidos
    public function detallespedidos()
    {
        return $this->hasMany(Detallespedidos::class, 'libro_id');
    }
    
}
