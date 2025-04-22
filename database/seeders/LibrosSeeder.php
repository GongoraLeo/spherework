<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Trait para deshabilitar eventos de modelo durante el seeding.
use Illuminate\Database\Seeder; // Clase base para los seeders.
use App\Models\Libros; // Modelo Libros para interactuar con la tabla.

/**
 * Class LibrosSeeder
 *
 * Seeder encargado de poblar la tabla `libros` con datos iniciales.
 * Primero elimina todos los registros existentes en la tabla y luego
 * inserta un conjunto predefinido de libros, asociándolos a IDs
 * de autores y editoriales que se asume existen previamente.
 *
 * @package Database\Seeders
 */
class LibrosSeeder extends Seeder
{
    /**
     * Ejecuta las operaciones de seeding para la tabla `libros`.
     *
     * Este método primero vacía la tabla `libros` utilizando `Libros::query()->delete()`
     * para asegurar un estado limpio antes de insertar nuevos datos.
     * A continuación, utiliza el método `Libros::create()` repetidamente para insertar
     * varios registros de libros específicos. Para cada libro, se proporcionan
     * los datos como `titulo`, `isbn`, `anio_publicacion`, `precio`, y los
     * IDs `autor_id` y `editorial_id`. Se asume que estos IDs corresponden a
     * registros válidos creados previamente por `AutoresSeeder` y `EditorialesSeeder`.
     *
     * @return void
     */
    public function run(): void
    {
        // Elimina todos los registros existentes en la tabla 'libros'.
        Libros::query()->delete();

        // Crea registros individuales para cada libro especificado.
        // Se asume que los autor_id y editorial_id corresponden a registros existentes.
        Libros::create([
            'titulo' => 'Cien años de soledad',
            'isbn' => '978-8437604947',
            'anio_publicacion' => 1967,
            'precio' => 19.95,
            'autor_id' => 1, // Asume que el autor con ID 1 existe.
            'editorial_id' => 2, // Asume que la editorial con ID 2 existe.
        ]);

        Libros::create([
            'titulo' => 'La casa de los espíritus',
            'isbn' => '978-8401341910',
            'anio_publicacion' => 1982,
            'precio' => 18.50,
            'autor_id' => 2, // Asume que el autor con ID 2 existe.
            'editorial_id' => 1, // Asume que la editorial con ID 1 existe.
        ]);

        Libros::create([
            'titulo' => 'Tokio Blues (Norwegian Wood)',
            'isbn' => '978-8483835043',
            'anio_publicacion' => 1987,
            'precio' => 21.00,
            'autor_id' => 3, // Asume que el autor con ID 3 existe.
            'editorial_id' => 3, // Asume que la editorial con ID 3 existe.
        ]);

        Libros::create([
            'titulo' => 'Mansfield Park',
            'isbn' => '978-8490650295',
            'anio_publicacion' => 1814,
            'precio' => 19.00,
            'autor_id' => 4, // Asume que el autor con ID 4 existe.
            'editorial_id' => 6, // Asume que la editorial con ID 6 existe.
        ]);

        Libros::create([
            'titulo' => 'Oposición',
            'isbn' => '978-8433929686',
            'anio_publicacion' => 2025,
            'precio' => 24.00,
            'autor_id' => 5, // Asume que el autor con ID 5 existe.
            'editorial_id' => 3, // Asume que la editorial con ID 3 existe.
        ]);

        Libros::create([
            'titulo' => 'La carretera',
            'isbn' => '978-8483468685',
            'anio_publicacion' => 2007,
            'precio' => 12.00,
            'autor_id' => 6, // Asume que el autor con ID 6 existe.
            'editorial_id' => 5, // Asume que la editorial con ID 5 existe.
        ]);

        Libros::create([
            'titulo' => 'La paciencia del agua sobre cada piedra',
            'isbn' => '978-8412664720',
            'anio_publicacion' => 2022,
            'precio' => 19.00,
            'autor_id' => 7, // Asume que el autor con ID 7 existe.
            'editorial_id' => 4, // Asume que la editorial con ID 4 existe.
        ]);

        Libros::create([
            'titulo' => 'Cronica de una muerte anunciada',
            'isbn' => '978-8497592437',
            'anio_publicacion' => 1981,
            'precio' => 12.00,
            'autor_id' => 1, // Asume que el autor con ID 1 existe.
            'editorial_id' => 5, // Asume que la editorial con ID 5 existe.
        ]);

        Libros::create([
            'titulo' => 'De que hablo cuando hablo de escribir',
            'isbn' => '843-2715092476',
            'anio_publicacion' => 2015,
            'precio' => 24.00,
            'autor_id' => 3, // Asume que el autor con ID 3 existe.
            'editorial_id' => 2, // Asume que la editorial con ID 2 existe.
        ]);
    }
}
