<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Assignment 2: route named after the first letter of my name (Ahmed -> /a)
Route::get('/a', function () {
    return view('ahmed');
});
