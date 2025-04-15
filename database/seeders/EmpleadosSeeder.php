<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Empleados;
use Illuminate\Support\Facades\Hash;

class EmpleadosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Empleados::query()->delete();

        Empleados::create([
            'nombre' => 'Admin Principal',
            'email' => 'admin.empleado@spherework.com',
            'password' => Hash::make('password'), // ¡Importante hashear!
            'rol' => 'administrador',
        ]);

        Empleados::create([
            'nombre' => 'Gestor Tienda',
            'email' => 'gestor.empleado@spherework.com',
            'password' => Hash::make('password'),
            'rol' => 'gestor',
        ]);
    }
}
