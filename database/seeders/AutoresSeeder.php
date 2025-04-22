<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Trait para deshabilitar eventos de modelo durante el seeding.
use Illuminate\Database\Seeder; // Clase base para los seeders.
use App\Models\Autores; // Modelo Autores para interactuar con la tabla.

/**
 * Class AutoresSeeder
 *
 * Seeder encargado de poblar la tabla `autores` con datos iniciales.
 * Primero elimina todos los registros existentes en la tabla y luego
 * inserta un conjunto predefinido de autores.
 *
 * @package Database\Seeders
 */
class AutoresSeeder extends Seeder
{
    /**
     * Ejecuta las operaciones de seeding para la tabla `autores`.
     *
     * Este método primero vacía la tabla `autores` utilizando `Autores::query()->delete()`
     * para asegurar un estado limpio antes de insertar nuevos datos.
     * A continuación, utiliza el método `Autores::create()` para insertar
     * varios registros de autores específicos, proporcionando el nombre y país
     * para cada uno.
     *
     * @return void
     */
    public function run(): void
    {
        // Elimina todos los registros existentes en la tabla 'autores'.
        // Se usa query()->delete() para asegurar que se ejecute correctamente.
        Autores::query()->delete();

        // Crea registros individuales para cada autor especificado.
        Autores::create(['nombre' => 'Gabriel García Márquez', 'pais' => 'Colombia']);
        Autores::create(['nombre' => 'Isabel Allende', 'pais' => 'Chile']);
        Autores::create(['nombre' => 'Haruki Murakami', 'pais' => 'Japón']);
        Autores::create(['nombre' => 'Jane Austen', 'pais' => 'Reino Unido']);
        Autores::create(['nombre' => 'Sara Mesa', 'pais' => 'Espana']); // Nota: 'Espana' sin 'ñ'.
        Autores::create(['nombre' => 'Cormac McCarthy', 'pais' => 'Estados Unidos']);
        Autores::create(['nombre' => 'Alejandra Kamiya', 'pais' => 'Argentina']);
    }
}
