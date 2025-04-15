<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Comentarios;

class ComentarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Comentarios::query()->delete();

        // Asume IDs de clientes y libros
        Comentarios::create([
            'cliente_id' => 1, // Juan Pérez
            'libro_id' => 1,   // Cien años de soledad
            'comentario' => 'Una obra maestra absoluta. Imprescindible.',
            'puntuacion' => 5,
            'fecha' => now()->subDays(10),
        ]);

        Comentarios::create([
            'cliente_id' => 2, // Maria Gomez
            'libro_id' => 1,   // Cien años de soledad
            'comentario' => 'Me costó un poco al principio, pero es genial.',
            'puntuacion' => 4,
            'fecha' => now()->subDays(8),
        ]);

         Comentarios::create([
            'cliente_id' => 1, // Juan Pérez
            'libro_id' => 3,   // Tokio Blues
            'comentario' => 'Melancólico y hermoso.',
            'puntuacion' => 5,
            'fecha' => now()->subDays(2),
        ]);
    }
}

