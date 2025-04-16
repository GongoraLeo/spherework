<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany; // Importa HasMany

// Importa los modelos relacionados
use App\Models\Pedidos;
use App\Models\Comentarios;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'rol' => 'string',
        ];
    }

    // --- NUEVAS RELACIONES AÑADIDAS ---

    /**
     * Obtiene los pedidos asociados al usuario.
     * Nota: Usa 'cliente_id' como clave foránea según tu migración de pedidos.
     */
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedidos::class, 'cliente_id'); // Usa 'cliente_id'
    }

    /**
     * Obtiene los comentarios asociados al usuario.
     * Nota: Usa 'user_id' como clave foránea según tu migración de comentarios.
     */
    public function comentarios(): HasMany
    {
        return $this->hasMany(Comentarios::class, 'user_id'); // Usa 'user_id'
    }

    // --- FIN NUEVAS RELACIONES ---
}
