<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Libros;

class LibrosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Libros::query()->delete();

        Libros::create([
            'titulo' => 'Cien años de soledad',
            'isbn' => '978-8437604947',
            'anio_publicacion' => 1967,
            'precio' => 19.95,
            'autor_id' => 1, // ID de García Márquez
            'editorial_id' => 2, // ID de Penguin Random House (ejemplo)
        ]);

        Libros::create([
            'titulo' => 'La casa de los espíritus',
            'isbn' => '978-8401341910',
            'anio_publicacion' => 1982,
            'precio' => 18.50,
            'autor_id' => 2, // ID de Isabel Allende
            'editorial_id' => 1, // ID de Planeta (ejemplo)
        ]);

         Libros::create([
            'titulo' => 'Tokio Blues (Norwegian Wood)',
            'isbn' => '978-8483835043',
            'anio_publicacion' => 1987,
            'precio' => 21.00,
            'autor_id' => 3, // ID de Murakami
            'editorial_id' => 3, // ID de Anagrama (ejemplo)
        ]);
    }
}
