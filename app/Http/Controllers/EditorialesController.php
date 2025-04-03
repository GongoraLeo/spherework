<?php

namespace App\Http\Controllers;

use App\Models\Editoriales;
use Illuminate\Http\Request;

class EditorialesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $editoriales = Editoriales::all();
        return view('editoriales.index', compact('editoriales'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('editoriales.create');
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

        Editoriales::create($request->all());

        return redirect()->route('editoriales.index')
            ->with('success', 'Editorial creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Editoriales $editoriales)
    {
        $editoriales = Editoriales::find($editoriales->id);
        return view('editoriales.show', compact('editoriales'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Editoriales $editoriales)
    {
        $editoriales = Editoriales::find($editoriales->id);
        return view('editoriales.edit', compact('editoriales'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Editoriales $editoriales)
    {
        $request->validate([
            'nombre' => 'required|max:255',
            'pais' => 'required|max:255'
        ]);

        $editoriales->update($request->all());

        return redirect()->route('editoriales.index')
            ->with('success', 'Editorial actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Editoriales $editoriales)
    {
        $editoriales = Editoriales::find($editoriales->id);
        $editoriales->delete();

        return redirect()->route('editoriales.index')
            ->with('success', 'Editorial eliminada correctamente.');
    }
}
