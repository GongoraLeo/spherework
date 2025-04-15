<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Editoriales;

class EditorialesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Editoriales::query()->delete();

        Editoriales::create(['nombre' => 'Editorial Planeta', 'pais' => 'España']);
        Editoriales::create(['nombre' => 'Penguin Random House', 'pais' => 'Internacional']);
        Editoriales::create(['nombre' => 'Anagrama', 'pais' => 'España']);
    }
}
