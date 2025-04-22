<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Trait para deshabilitar eventos de modelo durante el seeding.
use Illuminate\Database\Seeder; // Clase base para los seeders.
use App\Models\Detallespedidos; // Modelo Detallespedidos para interactuar con la tabla.
use App\Models\Libros; // Modelo Libros para obtener el precio de los libros.
use App\Models\Pedidos; // Importar Pedidos para obtener IDs válidos (aunque aquí se usan IDs fijos).

/**
 * Class DetallespedidosSeeder
 *
 * Seeder encargado de poblar la tabla `detallespedidos` con datos iniciales.
 * Primero elimina todos los registros existentes en la tabla. Luego, asumiendo
 * que existen ciertos pedidos y libros (creados por sus respectivos seeders),
 * crea registros de detalles de pedido asociando libros específicos a pedidos
 * específicos, utilizando el precio actual del libro.
 *
 * @package Database\Seeders
 */
class DetallespedidosSeeder extends Seeder
{
    /**
     * Ejecuta las operaciones de seeding para la tabla `detallespedidos`.
     *
     * Este método primero vacía la tabla `detallespedidos` usando `Detallespedidos::query()->delete()`.
     * A continuación, busca instancias específicas del modelo `Libros` utilizando `Libros::find()`
     * con IDs fijos (1, 2, 3). Para cada libro encontrado, crea uno o más registros
     * en la tabla `detallespedidos` utilizando `Detallespedidos::create()`.
     * Asocia estos detalles a IDs de pedido fijos (1, 2, 3), asumiendo que estos pedidos
     * existen (creados por `PedidosSeeder`). La cantidad se especifica directamente,
     * y el `precio` se toma del atributo `precio` del modelo `Libros` encontrado.
     * Se utilizan bloques `if` para asegurar que el libro exista antes de intentar
     * crear el detalle asociado.
     *
     * @return void
     */
    public function run(): void
    {
        // Vacía la tabla 'detallespedidos' antes de insertar nuevos datos.
        Detallespedidos::query()->delete();

        // Asume IDs fijos para pedidos (1, 2, 3) y busca libros por ID fijo.
        // Se asume que estos IDs corresponden a registros creados por PedidosSeeder y LibrosSeeder.

        // Detalles para el Pedido con ID 1.
        $libro1 = Libros::find(1); // Busca el libro con ID 1.
        if ($libro1) { // Procede solo si se encontró el libro.
            // Crea un detalle para el pedido 1 con el libro 1.
            Detallespedidos::create([
                'pedido_id' => 1,           // Asocia al pedido con ID 1.
                'libro_id' => $libro1->id,  // Asocia al libro encontrado.
                'cantidad' => 1,            // Establece la cantidad.
                'precio' => $libro1->precio, // Usa el precio del modelo Libro.
            ]);
        }

        // Detalles para el Pedido con ID 2.
        $libro2 = Libros::find(2); // Busca el libro con ID 2.
        $libro3 = Libros::find(3); // Busca el libro con ID 3.
        if ($libro2) { // Procede solo si se encontró el libro 2.
            // Crea un detalle para el pedido 2 con el libro 2.
            Detallespedidos::create([
                'pedido_id' => 2,           // Asocia al pedido con ID 2.
                'libro_id' => $libro2->id,  // Asocia al libro encontrado.
                'cantidad' => 2,            // Establece la cantidad.
                'precio' => $libro2->precio, // Usa el precio del modelo Libro.
            ]);
        }
         if ($libro3) { // Procede solo si se encontró el libro 3.
            // Crea un detalle para el pedido 2 con el libro 3.
            Detallespedidos::create([
                'pedido_id' => 2,           // Asocia al pedido con ID 2.
                'libro_id' => $libro3->id,  // Asocia al libro encontrado.
                'cantidad' => 1,            // Establece la cantidad.
                'precio' => $libro3->precio, // Usa el precio del modelo Libro.
            ]);
        }

        // Detalles para el Pedido con ID 3 (asumido como carrito pendiente).
         if ($libro1) { // Reutiliza la variable $libro1 si aún es válida (o se vuelve a buscar).
            // Crea un detalle para el pedido 3 con el libro 1.
            Detallespedidos::create([
                'pedido_id' => 3,           // Asocia al pedido con ID 3.
                'libro_id' => $libro1->id,  // Asocia al libro encontrado.
                'cantidad' => 1,            // Establece la cantidad.
                'precio' => $libro1->precio, // Usa el precio del modelo Libro.
            ]);
        }
    }
}
