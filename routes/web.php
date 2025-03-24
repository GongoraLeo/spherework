<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/killo', function () {
    return 'Killo ke es lo ke dise tú que no hay quien tentienda!';
});