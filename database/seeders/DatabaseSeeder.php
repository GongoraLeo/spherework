<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder; // Clase base para los seeders.

/**
 * Class DatabaseSeeder
 *
 * Seeder principal de la aplicación.
 * Este seeder es el punto de entrada para ejecutar todos los demás seeders
 * definidos en la aplicación. Se encarga de llamar a los seeders individuales
 * en un orden específico para asegurar que las dependencias (claves foráneas)
 * se satisfagan correctamente.
 *
 * @package Database\Seeders
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Ejecuta los seeders de la base de datos de la aplicación.
     *
     * Llama a una secuencia de seeders individuales utilizando el método `call()`.
     * El orden de llamada es importante para respetar las dependencias entre tablas:
     * 1. `UserSeeder`: Crea usuarios (clientes y administradores).
     * 2. `AutoresSeeder`: Crea autores.
     * 3. `EditorialesSeeder`: Crea editoriales.
     * 4. `LibrosSeeder`: Crea libros, requiere que existan autores y editoriales.
     * 5. `PedidosSeeder`: Crea pedidos, requiere que existan usuarios (clientes).
     * 6. `DetallespedidosSeeder`: Crea detalles de pedidos, requiere que existan pedidos y libros.
     * 7. `ComentariosSeeder`: Crea comentarios, requiere que existan usuarios y libros.
     *
     * Este orden asegura que al crear registros con claves foráneas, los registros
     * referenciados ya existan en sus respectivas tablas.
     *
     * @return void
     */
    public function run(): void
    {
        // Ejecuta la lista de seeders especificada en el array.
        // El método `call` ejecuta cada seeder en el orden proporcionado.
        $this->call([
            UserSeeder::class,          // Crea usuarios primero.
            AutoresSeeder::class,       // Crea autores.
            EditorialesSeeder::class,   // Crea editoriales.
            LibrosSeeder::class,        // Crea libros (depende de autores y editoriales).
            PedidosSeeder::class,       // Crea pedidos (depende de usuarios).
            DetallespedidosSeeder::class, // Crea detalles (depende de pedidos y libros).
            ComentariosSeeder::class,   // Crea comentarios (depende de usuarios y libros).
        ]);
    }
}
