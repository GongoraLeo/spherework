<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Libros;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Editoriales extends Model
{
    use HasFactory;
    
    protected $table = 'editoriales';

    protected $fillable = [
        'nombre',
        'pais',
    ];

    // Relación uno a muchos con la tabla libros
    public function libros()
    {
        return $this->hasMany(Libros::class, 'editorial_id');
    }
}
