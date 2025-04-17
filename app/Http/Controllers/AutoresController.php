<?php

namespace App\Http\Controllers;

use App\Models\Autores;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Importo Auth para la autorización
use Illuminate\View\View;             // Para type hinting
use Illuminate\Http\RedirectResponse; // Para type hinting
use Illuminate\Support\Facades\Log;    // Para logs (útil en destroy)

class AutoresController extends Controller
{
    /**
     * Muestro una lista del recurso (autores) para el admin.
     * @return View|RedirectResponse
     */
    public function index(): View|RedirectResponse
    {
        // Autorización: Solo Admin
        if (Auth::user()->rol !== 'administrador') {
            return redirect()->route('profile.entry')->with('error', 'Acceso no autorizado.');
        }

        // La colección se llama $autores (plural), esto está bien.
        $autores = Autores::orderBy('nombre')->paginate(15);
        // Apunto a la vista dentro de admin
        return view('admin.autores.index', compact('autores'));
    }

    /**
     * Muestro el formulario para crear un nuevo recurso (autor).
     * @return View|RedirectResponse
     */
    public function create(): View|RedirectResponse
    {
        // Autorización: Solo Admin
        if (Auth::user()->rol !== 'administrador') {
            return redirect()->route('admin.autores.index')->with('error', 'No tienes permiso para crear autores.');
        }
        // Apunto a la vista dentro de admin
        return view('admin.autores.create');
    }

    /**
     * Almaceno un nuevo recurso (autor) creado en la base de datos.
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        // Autorización: Solo Admin
        if (Auth::user()->rol !== 'administrador') {
            abort(403, 'Acción no autorizada.');
        }

        $request->validate([
            'nombre' => 'required|string|max:255|unique:autores,nombre',
            'pais'   => 'required|string|max:255'
        ]);

        try {
            Autores::create($request->all());
            // Redirijo a la ruta del índice de admin
            return redirect()->route('admin.autores.index')
                ->with('success', 'Autor creado correctamente.');
        } catch (\Exception $e) {
            Log::error("Error al crear autor: " . $e->getMessage(), $request->all());
            return back()->with('error', 'Ocurrió un error al crear el autor.')->withInput();
        }
    }

    /**
     * Muestro el autor especificado (vista admin).
     * @param Autores $autores // Laravel inyecta el autor basado en el ID de la ruta, usando el nombre plural solicitado.
     * @return View|RedirectResponse
     */
    // ***** MODIFICADO: Parámetro y compact() usan $autores (plural) *****
    public function show(Autores $autores): View|RedirectResponse
    {
        // Autorización: Solo Admin
        if (Auth::user()->rol !== 'administrador') {
            return redirect()->route('profile.entry')->with('error', 'Acceso no autorizado.');
        }
        // Apunto a la vista dentro de admin, pasando la variable como 'autores'
        return view('admin.autores.show', compact('autores'));
    }

    /**
     * Muestro el formulario para editar el autor especificado (vista admin).
     * @param Autores $autores // Usando el nombre plural solicitado.
     * @return View|RedirectResponse
     */
    // ***** MODIFICADO: Parámetro y compact() usan $autores (plural) *****
    public function edit(Autores $autores): View|RedirectResponse
    {
        // Autorización: Solo Admin
        if (Auth::user()->rol !== 'administrador') {
            return redirect()->route('admin.autores.index')->with('error', 'No tienes permiso para editar autores.');
        }
        // Apunto a la vista dentro de admin, pasando la variable como 'autores'
        return view('admin.autores.edit', compact('autores'));
    }

    /**
     * Actualizo el autor especificado en la base de datos.
     * @param Request $request
     * @param Autores $autores // Usando el nombre plural solicitado.
     * @return RedirectResponse
     */
    // ***** MODIFICADO: Parámetro y uso interno usan $autores (plural) *****
    public function update(Request $request, Autores $autores): RedirectResponse
    {
        // Autorización: Solo Admin
        if (Auth::user()->rol !== 'administrador') {
            abort(403, 'Acción no autorizada.');
        }

        $request->validate([
            // Validar que el nombre sea único, ignorando el propio autor actual
            'nombre' => 'required|string|max:255|unique:autores,nombre,' . $autores->id, // Usa $autores->id
            'pais'   => 'required|string|max:255'
        ]);

        try {
            $autores->update($request->all()); // Usa $autores->update()
            // Redirijo a la ruta del índice de admin
            return redirect()->route('admin.autores.index')
                ->with('success', 'Autor actualizado correctamente.');
        } catch (\Exception $e) {
            Log::error("Error al actualizar autor ID {$autores->id}: " . $e->getMessage(), $request->all()); // Usa $autores->id
            return back()->with('error', 'Ocurrió un error al actualizar el autor.')->withInput();
        }
    }

    /**
     * Elimino el autor especificado de la base de datos.
     * @param Autores $autores // Usando el nombre plural solicitado.
     * @return RedirectResponse
     */
    // ***** MODIFICADO: Parámetro y uso interno usan $autores (plural) *****
    public function destroy(Autores $autores): RedirectResponse
    {
        // Autorización: Solo Admin
        if (Auth::user()->rol !== 'administrador') {
            abort(403, 'Acción no autorizada.');
        }

        try {
            // Consideración: Verificar si el autor tiene libros asociados antes de borrar.
            if ($autores->libros()->count() > 0) { // Usa $autores->libros()
                 return redirect()->route('admin.autores.index')
                    ->with('error', 'No se puede eliminar el autor porque tiene libros asociados.');
            }
            $autores->delete(); // Usa $autores->delete()
            // Redirijo a la ruta del índice de admin
            return redirect()->route('admin.autores.index')
                ->with('success', 'Autor eliminado correctamente.');
        } catch (\Exception $e) {
            Log::error("Error al eliminar autor ID {$autores->id}: " . $e->getMessage()); // Usa $autores->id
            return redirect()->route('admin.autores.index')
                ->with('error', 'Ocurrió un error al eliminar el autor.');
        }
    }
}
