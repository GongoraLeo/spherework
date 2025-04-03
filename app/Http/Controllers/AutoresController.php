<?php

namespace App\Http\Controllers;

use App\Models\Autores;
use Illuminate\Http\Request;
use IlluminateSupportFacadesRoute;
use AppHttpControllersAutoresController;

class AutoresController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $autores = Autores::all();
        return view('autores.index', compact('autores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('autores.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|max:255',
            'pais' => 'required|max:255'
        ]);
        Autores::create($request->all());
        return redirect()->route('autores.index')
        ->with('success', 'Autor creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Autores $autores)
    {
        $autores = Autores::find($autores->id);
        return view('autores.show', compact('autores'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Autores $autores)
    {
        $autores = Autores::find($autores->id);
        return view('autores.edit', compact('autores'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Autores $autores)
    {
        $request->validate([
            'nombre' => 'required|max:255',
            'pais' => 'required|max:255'
        ]);
        $autores = Autores::find($autores->id);
        $autores->update($request->all());
        return redirect()->route('autores.index')
        ->with('success', 'Autor actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Autores $autores)
    {
        $autores = Autores::find($autores->id);
        $autores->delete();
        return redirect()->route('autores.index')
        ->with('success', 'Autor eliminado correctamente.');
    }
}
