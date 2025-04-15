<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Importar DB
use Illuminate\Support\Facades\Hash; // Importar Hash
use App\Models\User; // Importar User

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Opcional: Vaciar la tabla antes de sembrar
        User::query()->delete(); // O DB::table('users')->truncate();

        User::create([
            'name' => 'admin',
            'email' => 'admin@spherework.com',
            'password' => Hash::make('adminpassword'),
            'rol' => 'administrador',
        ]);

        User::create([
            'name' => 'cliente',
            'email' => 'cliente@spherework.com',
            'password' => Hash::make('clientepassword'),
            'rol' => 'cliente',
        ]);

        // Puedes añadir más usuarios o usar factories
        // \App\Models\User::factory(10)->create();
    }
}
