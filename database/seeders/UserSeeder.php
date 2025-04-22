<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Trait para deshabilitar eventos de modelo durante el seeding.
use Illuminate\Database\Seeder; // Clase base para los seeders.
use Illuminate\Support\Facades\DB; // Fachada DB (aunque se usa Eloquent para borrar).
use Illuminate\Support\Facades\Hash; // Fachada Hash para hashear contraseñas.
use App\Models\User; // Modelo User para interactuar con la tabla.

/**
 * Class UserSeeder
 *
 * Seeder encargado de poblar la tabla `users` con datos iniciales.
 * Crea un usuario administrador y un usuario cliente predefinidos.
 * Opcionalmente, vacía la tabla antes de la inserción.
 *
 * @package Database\Seeders
 */
class UserSeeder extends Seeder
{
    /**
     * Ejecuta las operaciones de seeding para la tabla `users`.
     *
     * Este método primero elimina todos los registros existentes en la tabla `users`
     * utilizando `User::query()->delete()`.
     * A continuación, crea dos usuarios específicos:
     * 1. Un usuario con rol 'administrador', nombre 'admin', email 'admin@spherework.com'
     *    y una contraseña predefinida ('adminpassword') hasheada mediante `Hash::make()`.
     * 2. Un usuario con rol 'cliente', nombre 'cliente', email 'cliente@spherework.com'
     *    y una contraseña predefinida ('clientepassword') hasheada mediante `Hash::make()`.
     *
     * @return void
     */
    public function run(): void
    {
        // Vacía la tabla 'users' antes de insertar nuevos datos.
        User::query()->delete();

        // Crea el usuario administrador.
        User::create([
            'name' => 'admin', // Nombre del usuario.
            'email' => 'admin@spherework.com', // Email del usuario.
            'password' => Hash::make('adminpassword'), // Hashea la contraseña antes de guardarla.
            'rol' => 'administrador', // Asigna el rol de administrador.
        ]);

        // Crea el usuario cliente.
        User::create([
            'name' => 'cliente', // Nombre del usuario.
            'email' => 'cliente@spherework.com', // Email del usuario.
            'password' => Hash::make('clientepassword'), // Hashea la contraseña antes de guardarla.
            'rol' => 'cliente', // Asigna el rol de cliente.
        ]);
    }
}
