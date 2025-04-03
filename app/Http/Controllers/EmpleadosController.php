<?php

namespace App\Http\Controllers;

use App\Models\Empleados;
use Illuminate\Http\Request;

class EmpleadosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $empleados = Empleados::all();
        return view('empleados.index', compact('empleados'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('empleados.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|min:8',
            'rol' => 'required|in:administrador,gestor'
        ]);
        Empleados::create($request->all());
        return redirect()->route('empleados.index')
            ->with('success', 'Empleado creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Empleados $empleados)
    {
        $empleados = Empleados::find($empleados->id);
        return view('empleados.show', compact('empleados'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Empleados $empleados)
    {
        $empleados = Empleados::find($empleados->id);
        return view('empleados.edit', compact('empleados'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Empleados $empleados)
    {
        $request->validate([
            'nombre' => 'required|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|min:8',
            'rol' => 'required|in:administrador,gestor'
        ]);
        $empleados->update($request->all());
        return redirect()->route('empleados.index')
            ->with('success', 'Empleado actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Empleados $empleados)
    {
        $empleados = Empleados::find($empleados->id);
        $empleados->delete();
        return redirect()->route('empleados.index')
            ->with('success', 'Empleado eliminado correctamente.');
    }
}
