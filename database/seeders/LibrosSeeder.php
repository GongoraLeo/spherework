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
            'autor_id' => 1,
            'editorial_id' => 2,
        ]);

        Libros::create([
            'titulo' => 'La casa de los espíritus',
            'isbn' => '978-8401341910',
            'anio_publicacion' => 1982,
            'precio' => 18.50,
            'autor_id' => 2,
            'editorial_id' => 1,
        ]);

        Libros::create([
            'titulo' => 'Tokio Blues (Norwegian Wood)',
            'isbn' => '978-8483835043',
            'anio_publicacion' => 1987,
            'precio' => 21.00,
            'autor_id' => 3,
            'editorial_id' => 3,
        ]);

        Libros::create([
            'titulo' => 'Mansfield Park',
            'isbn' => '978-8490650295',
            'anio_publicacion' => 1814,
            'precio' => 19.00,
            'autor_id' => 4,
            'editorial_id' => 6,
        ]);

        Libros::create([
            'titulo' => 'Oposición',
            'isbn' => '978-8433929686',
            'anio_publicacion' => 2025,
            'precio' => 24.00,
            'autor_id' => 5,
            'editorial_id' => 3,
        ]);

        Libros::create([
            'titulo' => 'La carretera',
            'isbn' => '978-8483468685',
            'anio_publicacion' => 2007,
            'precio' => 12.00,
            'autor_id' => 6,
            'editorial_id' => 5,
        ]);

        Libros::create([
            'titulo' => 'La paciencia del agua sobre cada piedra',
            'isbn' => '978-8412664720',
            'anio_publicacion' => 2022,
            'precio' => 19.00,
            'autor_id' => 7,
            'editorial_id' => 4,
        ]);

        Libros::create([
            'titulo' => 'Cronica de una muerte anunciada',
            'isbn' => '978-8497592437',
            'anio_publicacion' => 1981,
            'precio' => 12.00,
            'autor_id' => 1,
            'editorial_id' => 5,
        ]);

        Libros::create([
            'titulo' => 'De que hablo cuando hablo de escribir',
            'isbn' => '843-2715092476',
            'anio_publicacion' => 2015,
            'precio' => 24.00,
            'autor_id' => 3,
            'editorial_id' => 2,
        ]);
    }
}
