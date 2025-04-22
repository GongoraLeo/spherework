<?php
// database/seeders/ComentariosSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Trait para deshabilitar eventos de modelo durante el seeding.
use Illuminate\Database\Seeder; // Clase base para los seeders.
use App\Models\Comentarios; // Modelo Comentarios para interactuar con la tabla.
use App\Models\User;  // Modelo User para obtener IDs de usuarios existentes.
use App\Models\Libros; // Modelo Libros para obtener IDs de libros existentes.

/**
 * Class ComentariosSeeder
 *
 * Seeder encargado de poblar la tabla `comentarios` con datos iniciales.
 * Primero elimina todos los registros existentes en la tabla. Luego, busca
 * usuarios y libros específicos (creados por sus respectivos seeders) y,
 * si los encuentra, crea comentarios asociados a ellos.
 *
 * @package Database\Seeders
 */
class ComentariosSeeder extends Seeder
{
    /**
     * Ejecuta las operaciones de seeding para la tabla `comentarios`.
     *
     * Este método primero vacía la tabla `comentarios` usando `Comentarios::query()->delete()`.
     * A continuación, intenta obtener instancias específicas de `User` (por email) y `Libros` (por ISBN)
     * que se asume han sido creadas previamente por `UserSeeder` y `LibrosSeeder`.
     * Utiliza bloques `if` para verificar que tanto el usuario como el libro buscado existan
     * antes de intentar crear un comentario con `Comentarios::create()`. Si alguna de las
     * entidades requeridas no se encuentra, se emite una advertencia en la consola
     * utilizando `$this->command->warn()`. Se crean dos comentarios de ejemplo si
     * las condiciones se cumplen.
     *
     * @return void
     */
    public function run(): void
    {
        // Vacía la tabla 'comentarios' antes de insertar nuevos datos.
        Comentarios::query()->delete();

        // Busca un usuario específico por su email. Se asume que UserSeeder lo creó.
        $userCliente1 = User::where('email', 'cliente@spherework.com')->first();
        // Busca un libro específico por su ISBN. Se asume que LibrosSeeder lo creó.
        $libro1 = Libros::where('isbn', '978-8437604947')->first(); // Cien años de soledad
        // Busca otro libro específico por su ISBN.
        $libro3 = Libros::where('isbn', '978-8483835043')->first(); // Tokio Blues

        // Intenta crear el primer comentario solo si se encontraron el usuario y el libro correspondientes.
        if ($userCliente1 && $libro1) {
            // Crea un registro en la tabla 'comentarios' usando los IDs obtenidos.
            Comentarios::create([
                'user_id'    => $userCliente1->id, // Asigna el ID del usuario encontrado.
                'libro_id'   => $libro1->id,       // Asigna el ID del libro encontrado.
                'comentario' => 'Una obra maestra absoluta. Imprescindible.', // Texto del comentario.
                'puntuacion' => 5,                // Puntuación asignada.
            ]);

            // Bloque comentado para añadir otro comentario (no se documenta por estar comentado).
            // ...

        } else {
             // Si no se encontró el usuario o el libro, muestra una advertencia en la consola.
             $this->command->warn('Usuario cliente@spherework.com o Libro 1 no encontrado. No se creó el primer comentario.');
        }

        // Intenta crear el segundo comentario solo si se encontraron el usuario y el libro correspondientes.
        if ($userCliente1 && $libro3) {
             // Crea otro registro en la tabla 'comentarios'.
             Comentarios::create([
                'user_id'    => $userCliente1->id, // Asigna el ID del usuario encontrado.
                'libro_id'   => $libro3->id,       // Asigna el ID del libro encontrado.
                'comentario' => 'Melancólico y hermoso.', // Texto del comentario.
                'puntuacion' => 5,                // Puntuación asignada.
            ]);
        } else {
             // Si no se encontró el usuario o el libro, muestra una advertencia en la consola.
             $this->command->warn('Usuario cliente@spherework.com o Libro 3 no encontrado. No se creó el segundo comentario.');
        }

    }
}
