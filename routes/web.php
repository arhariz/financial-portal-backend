<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/health', fn () => response()->json([
    'status' => 'ok',
    'app' => config('app.name'),
    'environment' => app()->environment(),
    'php' => PHP_VERSION,
    'laravel' => app()->version(),
    'timestamp' => now()->toIso8601String(),
]));
