<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Trait para deshabilitar eventos de modelo durante el seeding.
use Illuminate\Database\Seeder; // Clase base para los seeders.
use App\Models\Editoriales; // Modelo Editoriales para interactuar con la tabla.

/**
 * Class EditorialesSeeder
 *
 * Seeder encargado de poblar la tabla `editoriales` con datos iniciales.
 * Primero elimina todos los registros existentes en la tabla y luego
 * inserta un conjunto predefinido de editoriales.
 *
 * @package Database\Seeders
 */
class EditorialesSeeder extends Seeder
{
    /**
     * Ejecuta las operaciones de seeding para la tabla `editoriales`.
     *
     * Este método primero vacía la tabla `editoriales` utilizando `Editoriales::query()->delete()`
     * para asegurar un estado limpio antes de insertar nuevos datos.
     * A continuación, utiliza el método `Editoriales::create()` para insertar
     * varios registros de editoriales específicas, proporcionando el nombre y país
     * para cada una.
     *
     * @return void
     */
    public function run(): void
    {
        // Elimina todos los registros existentes en la tabla 'editoriales'.
        Editoriales::query()->delete();

        // Crea registros individuales para cada editorial especificada.
        Editoriales::create(['nombre' => 'Editorial Planeta', 'pais' => 'España']);
        Editoriales::create(['nombre' => 'Penguin Random House', 'pais' => 'Internacional']);
        Editoriales::create(['nombre' => 'Anagrama', 'pais' => 'España']);
        Editoriales::create(['nombre' => 'Paginas de espuma', 'pais' => 'España']);
        Editoriales::create(['nombre' => 'Debolsillo', 'pais' => 'España']);
        Editoriales::create(['nombre' => 'Alba', 'pais' => 'España']);
    }
}
