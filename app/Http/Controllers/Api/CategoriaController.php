<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Resources\CategoriaResource;
use App\Http\Resources\CategoriaCollection;

use App\Models\Categoria;

class CategoriaController extends Controller
{
    public function index(){
        return new CategoriaCollection(Categoria::all());
    }

    public function show(Categoria $categoria){
        $categoria = $categoria->load('recetas');
        return new CategoriaResource($categoria);
    }

}
