<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            AutoresSeeder::class,
            EditorialesSeeder::class,
            LibrosSeeder::class,       // Después de Autores y Editoriales
            PedidosSeeder::class,      // Después de Clientes (y Empleados si aplica)
            DetallespedidosSeeder::class, // Después de Pedidos y Libros
            ComentariosSeeder::class,  // Después de Clientes y Libros
        ]);

    }
}
