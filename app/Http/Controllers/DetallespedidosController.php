<?php

namespace App\Http\Controllers;

use App\Models\Detallespedidos;
use Illuminate\Http\Request;

class DetallespedidosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $detallespedidos = Detallespedidos::all();
        return view('detallespedidos.index', compact('detallespedidos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('detallespedidos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'pedido_id' => 'required|exists:pedidos,id',
            'libro_id' => 'required|exists:libros,id',
            'cantidad' => 'required|integer|min:1',
            'precio' => 'required|numeric|min:0',
        ]);

        Detallespedidos::create($request->all());

        return redirect()->route('detallespedidos.index')
            ->with('success', 'Detalle de pedido creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Detallespedidos $detallespedidos)
    {
        $detallespedidos = Detallespedidos::find($detallespedidos->id);
        return view('detallespedidos.show', compact('detallespedidos'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Detallespedidos $detallespedidos)
    {
        $detallespedidos = Detallespedidos::find($detallespedidos->id);
        return view('detallespedidos.edit', compact('detallespedidos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Detallespedidos $detallespedidos)
    {
        $request->validate([
            'pedido_id' => 'required|exists:pedidos,id',
            'libro_id' => 'required|exists:libros,id',
            'cantidad' => 'required|integer|min:1',
            'precio' => 'required|numeric|min:0',
        ]);

        $detallespedidos = Detallespedidos::find($detallespedidos->id);
        $detallespedidos->update($request->all());

        return redirect()->route('detallespedidos.index')
            ->with('success', 'Detalle de pedido actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Detallespedidos $detallespedidos)
    {
        $detallespedidos = Detallespedidos::find($detallespedidos->id);
        $detallespedidos->delete();

        return redirect()->route('detallespedidos.index')
            ->with('success', 'Detalle de pedido eliminado correctamente.');
    }
}
