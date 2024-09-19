<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Categoria;

class CategoriaController extends Controller
{
    public function index(){
        return Categoria::all();
    }

    public function show(Categoria $categoria){
        return $categoria->load('recetas');
    }

}
