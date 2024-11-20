<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\RecetaController;
use App\Http\Controllers\Api\EtiquetaController;
use App\Http\Controllers\Api\LoginController;

Route::post('login', [LoginController::class, 'store']);

Route::options('{all:.*}', function(){
    return response()->json();
});

Route::middleware('auth:sanctum')->group(function(){
    Route::get('categorias', [CategoriaController::class, 'index']);
    Route::get('categorias/{categoria}', [CategoriaController::class, 'show']);
    
    Route::apiResource('recetas', RecetaController::class);
    
    Route::get('etiquetas', [EtiquetaController::class, 'index']);
    Route::get('etiquetas/{etiqueta}', [EtiquetaController::class, 'show']);

});


/*
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
*/