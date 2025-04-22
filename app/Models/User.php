<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail; // Interfaz para verificación de email (actualmente comentada).
use Illuminate\Database\Eloquent\Factories\HasFactory; // Trait para usar factories.
use Illuminate\Foundation\Auth\User as Authenticatable; // Clase base de usuario autenticable de Laravel.
use Illuminate\Notifications\Notifiable; // Trait para habilitar notificaciones.
use Illuminate\Database\Eloquent\Relations\HasMany; // Tipo de relación para pedidos() y comentarios().

// Importa los modelos relacionados para las relaciones Eloquent.
use App\Models\Pedidos;
use App\Models\Comentarios;

/**
 * Class User
 *
 * Representa un usuario en la aplicación, que puede ser un cliente o un administrador.
 * Este modelo extiende la clase `Authenticatable` de Laravel, proporcionando
 * funcionalidades de autenticación. Incluye atributos asignables masivamente,
 * atributos ocultos para serialización, conversiones de tipos y relaciones
 * con otros modelos como `Pedidos` y `Comentarios`.
 *
 * @property int $id Identificador único del usuario.
 * @property string $name Nombre del usuario.
 * @property string $email Dirección de correo electrónico única del usuario.
 * @property \Illuminate\Support\Carbon|null $email_verified_at Fecha y hora en que se verificó el email.
 * @property string $password Contraseña hasheada del usuario.
 * @property string|null $remember_token Token para la funcionalidad "recordarme".
 * @property string $rol Rol del usuario (ej. 'cliente', 'administrador').
 * @property \Illuminate\Support\Carbon|null $created_at Fecha y hora de creación.
 * @property \Illuminate\Support\Carbon|null $updated_at Fecha y hora de última actualización.
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Pedidos[] $pedidos Los pedidos asociados a este usuario (como cliente).
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Comentarios[] $comentarios Los comentarios realizados por este usuario.
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications Las notificaciones para este usuario.
 * @property-read int|null $notifications_count El número de notificaciones.
 *
 * @package App\Models
 */
class User extends Authenticatable
{
    /**
     * Trait HasFactory
     * Habilita el uso de factories para este modelo.
     *
     * Trait Notifiable
     * Habilita el sistema de notificaciones de Laravel para este modelo.
     *
     * @use HasFactory<\Database\Factories\UserFactory> Especifica la factory asociada.
     */
    use HasFactory, Notifiable;

    /**
     * Los atributos que son asignables masivamente.
     *
     * Define una lista blanca de columnas que pueden ser llenadas usando
     * los métodos `create` o `fill` / `update`.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol', // Atributo para diferenciar roles (cliente/administrador).
    ];

    /**
     * Los atributos que deben ocultarse para la serialización.
     *
     * Define los atributos que no deben incluirse cuando el modelo
     * se convierte a un array o JSON (ej. en respuestas de API).
     * Es una medida de seguridad, especialmente para la contraseña.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',       // Oculta la contraseña hasheada.
        'remember_token', // Oculta el token de "recordarme".
    ];

    /**
     * Obtiene los atributos que deben ser convertidos a tipos nativos.
     *
     * Define cómo ciertos atributos deben ser tratados por Eloquent.
     * Por ejemplo, asegura que `email_verified_at` sea un objeto Carbon,
     * `password` sea hasheado automáticamente al asignarse, y `rol` sea string.
     *
     * @return array<string, string> Un array asociativo de nombre de atributo a tipo.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime', // Convierte a objeto Carbon.
            'password' => 'hashed',           // Hashea automáticamente la contraseña.
            'rol' => 'string',               // Asegura que el rol sea tratado como string.
        ];
    }

    // --- RELACIONES ELOQUENT ---

    /**
     * Define la relación "uno a muchos" con el modelo Pedidos.
     *
     * Establece que una instancia de `User` (actuando como cliente) puede tener
     * asociados múltiples instancias de `Pedidos`. Permite acceder a la colección
     * de pedidos del usuario a través de la propiedad `$user->pedidos`.
     * La clave foránea utilizada en la tabla `pedidos` es `cliente_id`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Retorna el objeto de la relación HasMany.
     */
    public function pedidos(): HasMany
    {
        // Define que este usuario tiene muchos ('hasMany') Pedidos.
        // El segundo argumento 'cliente_id' es la clave foránea en la tabla 'pedidos'.
        return $this->hasMany(Pedidos::class, 'cliente_id');
    }

    /**
     * Define la relación "uno a muchos" con el modelo Comentarios.
     *
     * Establece que una instancia de `User` puede tener asociados
     * múltiples instancias de `Comentarios`. Permite acceder a la colección
     * de comentarios realizados por el usuario a través de la propiedad
     * `$user->comentarios`. La clave foránea utilizada en la tabla `comentarios`
     * es `user_id`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Retorna el objeto de la relación HasMany.
     */
    public function comentarios(): HasMany
    {
        // Define que este usuario tiene muchos ('hasMany') Comentarios.
        // El segundo argumento 'user_id' es la clave foránea en la tabla 'comentarios'.
        return $this->hasMany(Comentarios::class, 'user_id');
    }

    // --- FIN RELACIONES ---
}
