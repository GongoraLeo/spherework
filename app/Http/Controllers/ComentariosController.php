<?php

namespace App\Http\Controllers;

use App\Models\Comentarios;
use Illuminate\Http\Request;

class ComentariosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $comentarios = Comentarios::all();
        return view('comentarios.index', compact('comentarios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('comentarios.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'comentario' => 'required|max:255',
            'cliente_id' => 'required|exists:clientes,id',
            'puntuacion' => 'required|integer|min:1|max:5',
            'libro_id' => 'required|exists:libros,id',
            'fecha' => 'required|date'
        ]);
        Comentarios::create($request->all());
        return redirect()->route('comentarios.index')
            ->with('success', 'Comentario creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Comentarios $comentarios)
    {
        $comentarios = Comentarios::find($comentarios->id);
        return view('comentarios.show', compact('comentarios'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comentarios $comentarios)
    {
        $comentarios = Comentarios::find($comentarios->id);
        return view('comentarios.edit', compact('comentarios'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comentarios $comentarios)
    {
        $request->validate([
            'comentario' => 'required|max:255',
            'cliente_id' => 'required|exists:clientes,id',
            'puntuacion' => 'required|integer|min:1|max:5',
            'libro_id' => 'required|exists:libros,id',
            'fecha' => 'required|date'
        ]);
        $comentarios->update($request->all());
        return redirect()->route('comentarios.index')
            ->with('success', 'Comentario actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comentarios $comentarios)
    {
        $comentarios = Comentarios::find($comentarios->id);
        $comentarios->delete();
        return redirect()->route('comentarios.index')
            ->with('success', 'Comentario eliminado correctamente.');
    }
}
