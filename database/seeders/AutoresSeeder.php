<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Autores;

class AutoresSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Autores::query()->delete();

        Autores::create(['nombre' => 'Gabriel García Márquez', 'pais' => 'Colombia']);
        Autores::create(['nombre' => 'Isabel Allende', 'pais' => 'Chile']);
        Autores::create(['nombre' => 'Haruki Murakami', 'pais' => 'Japón']);
        Autores::create(['nombre' => 'Jane Austen', 'pais' => 'Reino Unido']);
    }
}
