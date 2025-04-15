<?php

namespace App\Http\Controllers;

use App\Models\Libros;
use Illuminate\Http\Request;
use App\Models\Autores;
use App\Models\Editoriales;

class LibrosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Carga eficiente de relaciones
        $libros = Libros::with(['autor', 'editorial'])->orderBy('titulo')->get();
        return view('libros.index', compact('libros'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Obtener todos los autores ordenados por nombre
        $autores = Autores::orderBy('nombre')->get();
        // Obtener todas las editoriales ordenadas por nombre
        $editoriales = Editoriales::orderBy('nombre')->get();

        // Pasar los autores y editoriales a la vista
        return view('libros.create', compact('autores', 'editoriales'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|max:255',
            'autor_id' => 'required|exists:autores,id',
            'editorial_id' => 'required|exists:editoriales,id',
            'anio_publicacion' => 'required|integer|min:1900|max:' . date('Y'),
            'isbn' => 'required|unique:libros,isbn|max:13',
            'precio' => 'required|numeric|min:0',

            ]);

        Libros::create($request->all());
        return redirect()->route('libros.index')
            ->with('success', 'Libro creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Libros $libros)
    {
        $libros = Libros::find($libros->id);
        return view('libros.show', compact('libros'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Libros $libros)
    {
        $libros = Libros::find($libros->id);
        return view('libros.edit', compact('libros'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Libros $libros)
    {
        $request->validate([
            'titulo' => 'required|max:255',
            'autor_id' => 'required|exists:autores,id',
            'editorial_id' => 'required|exists:editoriales,id',
            'anio_publicacion' => 'required|integer|min:1900|max:' . date('Y'),
            'isbn' => 'required|max:13|unique:libros,isbn,' . $libros->id,
        ]);

        $libros = Libros::find($libros->id);
        $libros->update($request->all());
        return redirect()->route('libros.index')
            ->with('success', 'Libro actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Libros $libros)
    {
        $libros = Libros::find($libros->id);
        $libros->delete();
        return redirect()->route('libros.index')
            ->with('success', 'Libro eliminado correctamente.');
    }
}
