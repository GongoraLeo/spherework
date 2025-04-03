<?php

namespace App\Http\Controllers;

use App\Models\Clientes;
use Illuminate\Http\Request;

class ClientesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clientes = Clientes::all();
        return view('clientes.index', compact('clientes'));
        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('clientes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|min:8'
        ]);
        Clientes::create($request->all());
        return redirect()->route('clientes.index')
        ->with('success', 'Cliente creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Clientes $clientes)
    {
        $clientes = Clientes::find($clientes->id);
        return view('clientes.show', compact('clientes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Clientes $clientes)
    {
        $clientes = Clientes::find($clientes->id);
        return view('clientes.edit', compact('clientes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Clientes $clientes)
    {
        $request->validate([
            'nombre' => 'required|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|min:8'
        ]);
        $clientes->update($request->all());
        return redirect()->route('clientes.index')
        ->with('success', 'Cliente actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Clientes $clientes)
    {
        $clientes = Clientes::find($clientes->id);
        $clientes->delete();
        return redirect()->route('clientes.index')
        ->with('success', 'Cliente eliminado correctamente.');
    }
}
