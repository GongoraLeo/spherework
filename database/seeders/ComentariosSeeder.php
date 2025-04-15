<?php
// database/seeders/ComentariosSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Comentarios;
use App\Models\User;  // Importar User para obtener IDs
use App\Models\Libros; // Importar Libros para obtener IDs

class ComentariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vaciar la tabla antes de sembrar
        Comentarios::query()->delete();

        // Obtener usuarios y libros (asegúrate que UserSeeder y LibrosSeeder se ejecuten antes)
        $userCliente1 = User::where('email', 'cliente@spherework.com')->first();
        // $userAdmin = User::where('email', 'admin@spherework.com')->first(); // Si admin también comenta
        $libro1 = Libros::where('isbn', '978-8437604947')->first(); // Cien años de soledad
        $libro3 = Libros::where('isbn', '978-8483835043')->first(); // Tokio Blues

        // Crear comentarios solo si los usuarios y libros existen
        if ($userCliente1 && $libro1) {
            Comentarios::create([
                // Usa las columnas CORRECTAS:
                'user_id'    => $userCliente1->id, // Usa user_id
                'libro_id'   => $libro1->id,
                'comentario' => 'Una obra maestra absoluta. Imprescindible.',
                'puntuacion' => 5,
                // 'fecha' => now()->subDays(10), // ELIMINADO - Se usa created_at
            ]);

            // Otro comentario para el mismo libro, si otro usuario existe
            // $otroUsuario = User::find(X); // Buscar otro usuario
            // if($otroUsuario) {
            //     Comentarios::create([
            //         'user_id'    => $otroUsuario->id,
            //         'libro_id'   => $libro1->id,
            //         'comentario' => 'Me costó un poco al principio, pero es genial.',
            //         'puntuacion' => 4,
            //     ]);
            // }

        } else {
             $this->command->warn('Usuario cliente@spherework.com o Libro 1 no encontrado. No se creó el primer comentario.');
        }

        if ($userCliente1 && $libro3) {
             Comentarios::create([
                'user_id'    => $userCliente1->id, // Usa user_id
                'libro_id'   => $libro3->id,
                'comentario' => 'Melancólico y hermoso.',
                'puntuacion' => 5,
                 // 'fecha' => now()->subDays(2), // ELIMINADO
            ]);
        } else {
             $this->command->warn('Usuario cliente@spherework.com o Libro 3 no encontrado. No se creó el segundo comentario.');
        }

        // Puedes añadir más comentarios aquí
    }
}
