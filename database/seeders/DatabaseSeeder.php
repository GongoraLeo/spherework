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
            ClientesSeeder::class,
            EmpleadosSeeder::class,
            AutoresSeeder::class,
            EditorialesSeeder::class,
            LibrosSeeder::class,       // Después de Autores y Editoriales
            PedidosSeeder::class,      // Después de Clientes (y Empleados si aplica)
            DetallespedidosSeeder::class, // Después de Pedidos y Libros
            ComentariosSeeder::class,  // Después de Clientes y Libros
        ]);

        // Ejemplo para sembrar la tabla pivote libros_clientes (si la necesitas)
        // $cliente1 = \App\Models\Clientes::find(1);
        // $libro1 = \App\Models\Libros::find(1);
        // $libro2 = \App\Models\Libros::find(2);
        // if ($cliente1 && $libro1 && $libro2) {
        //     $cliente1->libros()->attach([$libro1->id, $libro2->id]); // Asume relación 'libros' en Clientes
        // }
    }
}
