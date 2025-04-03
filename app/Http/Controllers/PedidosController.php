<?php

namespace App\Http\Controllers;

use App\Models\Pedidos;
use Illuminate\Http\Request;

class PedidosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pedidos = Pedidos::all();
        return view('pedidos.index', compact('pedidos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pedidos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'fecha' => 'required|date',
            'estado' => 'required|string|max:255'
        ]);

        Pedidos::create($request->all());
        return redirect()->route('pedidos.index')
            ->with('success', 'Pedido creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Pedidos $pedidos)
    {
        $pedidos = Pedidos::find($pedidos->id);
        return view('pedidos.show', compact('pedidos'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pedidos $pedidos)
    {
        $pedidos = Pedidos::find($pedidos->id);
        return view('pedidos.edit', compact('pedidos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pedidos $pedidos)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'fecha' => 'required|date',
            'estado' => 'required|string|max:255'
        ]);

        $pedidos->update($request->all());
        return redirect()->route('pedidos.index')
            ->with('success', 'Pedido actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pedidos $pedidos)
    {
        $pedidos = Pedidos::find($pedidos->id);
        $pedidos->delete();
        return redirect()->route('pedidos.index')
            ->with('success', 'Pedido eliminado correctamente.');
    }
}
