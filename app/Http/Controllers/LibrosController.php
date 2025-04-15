<?php

namespace App\Http\Controllers;

use App\Models\Libros;
use Illuminate\Http\Request;
use App\Models\Autores;
use App\Models\Editoriales;
use Illuminate\Support\Facades\Auth; // Necesario para la comprobación de rol
use Illuminate\View\View;             // Para type hinting
use Illuminate\Http\RedirectResponse; // Para type hinting

class LibrosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        // Cargar libros con sus relaciones para evitar N+1 queries en la vista index
        $libros = Libros::with(['autor', 'editorial'])->latest()->paginate(15); // O usa ->get() si no quieres paginación
        return view('libros.index', compact('libros'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create(): View|RedirectResponse
    {
        // Autorización: Solo administradores pueden crear
        if (!Auth::check() || Auth::user()->rol !== 'administrador') {
            return redirect()->route('libros.index')->with('error', 'No tienes permiso para añadir libros.');
            // abort(403, 'Acción no autorizada.');
        }

        $autores = Autores::orderBy('nombre')->get();
        $editoriales = Editoriales::orderBy('nombre')->get();
        return view('libros.create', compact('autores', 'editoriales'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        // Autorización: Solo administradores pueden guardar
        if (!Auth::check() || Auth::user()->rol !== 'administrador') {
             abort(403, 'Acción no autorizada.');
        }

        // Validación (basada en tu vista create.blade.php y modelo)
        $request->validate([
            'titulo' => 'required|string|max:255',
            'isbn' => 'required|string|max:13|unique:libros,isbn', // ISBN debe ser único en la tabla libros
            'anio_publicacion' => 'required|integer|min:1000|max:' . date('Y'),
            'precio' => 'required|numeric|min:0',
            'autor_id' => 'required|integer|exists:autores,id', // Asegura que el autor exista
            'editorial_id' => 'required|integer|exists:editoriales,id', // Asegura que la editorial exista
        ]);

        Libros::create($request->all());

        return redirect()->route('libros.index')
            ->with('success', 'Libro añadido correctamente.');
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Libros  $libros // Mantenemos $libros por Route Model Binding
     * @return \Illuminate\View\View
     */
    public function show(Libros $libros): View // Mantenemos $libros
    {
        // No necesitas Libros::find($libros->id); Laravel ya lo hizo.
        // Carga las relaciones si no se cargaron antes (aunque 'with' en index es mejor)
        $libros->loadMissing(['autor', 'editorial', 'comentarios.user']);
        return view('libros.show', compact('libros')); // Pasamos 'libros' a la vista
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Libros  $libros // Mantenemos $libros
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(Libros $libros): View|RedirectResponse // Mantenemos $libros
    {
        // Autorización: Solo administradores pueden editar.
        if (!Auth::check() || Auth::user()->rol !== 'administrador') {
             return redirect()->route('libros.index')->with('error', 'No tienes permiso para editar libros.');
             // abort(403, 'Acción no autorizada.');
        }

        // No necesitas Libros::find($libros->id); Laravel ya inyectó el modelo correcto en $libros.

        // Obtener los datos para los desplegables (selects)
        $autores = Autores::orderBy('nombre')->get();
        $editoriales = Editoriales::orderBy('nombre')->get();

        // Pasar el libro y las colecciones a la vista
        return view('libros.edit', compact('libros', 'autores', 'editoriales')); // Pasamos 'libros'
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Libros  $libros // Mantenemos $libros
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Libros $libros): RedirectResponse // Mantenemos $libros
    {
         // Autorización: Solo administradores pueden actualizar
        if (!Auth::check() || Auth::user()->rol !== 'administrador') {
             abort(403, 'Acción no autorizada.');
        }

        // Validación (ajustada para usar $libros->id en unique)
        $request->validate([
            'titulo' => 'required|string|max:255',
            'autor_id' => 'required|integer|exists:autores,id',
            'editorial_id' => 'required|integer|exists:editoriales,id',
            'anio_publicacion' => 'required|integer|min:1000|max:' . date('Y'),
            // Correcto para unique en update: ignora el ID del libro actual
            'isbn' => 'required|string|max:13|unique:libros,isbn,' . $libros->id,
            'precio' => 'required|numeric|min:0',
        ]);

        // No necesitas Libros::find($libros->id); Laravel ya lo hizo.
        $libros->update($request->all());

        // Puedes redirigir a show o index
        return redirect()->route('libros.index') // O route('libros.show', $libros)
            ->with('success', 'Libro actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Libros  $libros // Mantenemos $libros
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Libros $libros): RedirectResponse // Mantenemos $libros
    {
         // Autorización: Solo administradores pueden eliminar
        if (!Auth::check() || Auth::user()->rol !== 'administrador') {
             abort(403, 'Acción no autorizada.');
        }

        // No necesitas Libros::find($libros->id); Laravel ya lo hizo.
        try {
            $libros->delete();
             return redirect()->route('libros.index')
                ->with('success', 'Libro eliminado correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Capturar error si hay restricciones de clave foránea (ej: en detallespedidos)
            return redirect()->route('libros.index')
                ->with('error', 'No se puede eliminar el libro porque está asociado a pedidos existentes.');
        } catch (\Exception $e) {
             return redirect()->route('libros.index')
                ->with('error', 'Ocurrió un error al eliminar el libro.');
        }
    }
}
