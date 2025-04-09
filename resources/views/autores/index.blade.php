<!-- filepath: c:\xampp\htdocs\spherework\resources\views\autores\index.blade.php -->
@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Lista de Autores</h1>

    <ul>
        @foreach ($autores as $autor)
            <li>{{ $autor->nombre }}</li>
        @endforeach
    </ul>
@endsection