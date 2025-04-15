<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
// Podrías necesitar esto si los empleados se autentican:
// use Illuminate\Foundation\Auth\User as Authenticatable;
// use Illuminate\Notifications\Notifiable;

// Si los empleados NO se autentican, usa solo Model:
class Empleados extends Model // O extends Authenticatable si se loguean
{
    use HasFactory; // , Notifiable; // Añadir Notifiable si extiendes Authenticatable

    // Especifica la tabla si no sigue la convención 'empleados'
    protected $table = 'empleados';

    // Define los campos que se pueden asignar masivamente (basado en tu controller)
    protected $fillable = [
        'nombre',
        'email',
        'password', // ¡Considera hashear la contraseña en el controlador o usando un Mutator!
        'rol',
    ];

    /**
     * Los atributos que deben ocultarse para la serialización (si es Authenticatable).
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        // 'remember_token', // Si usas remember tokens
    ];

    /**
     * Los atributos que deben ser casteados (si es Authenticatable).
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // 'email_verified_at' => 'datetime', // Si tienes verificación de email
            'password' => 'hashed', // ¡Importante si extiendes Authenticatable y usas el hash automático!
        ];
    }


    /**
     * Define la relación con Pedidos.
     * Un empleado puede gestionar muchos pedidos.
     */
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedidos::class, 'empleado_id');
    }

    // Laravel maneja created_at y updated_at por defecto si existen en la tabla.
    // Si no existen, añade: public $timestamps = false;
}
