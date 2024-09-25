<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Resources\RecetaResource;

use App\Models\Receta;

class RecetaController extends Controller
{
    public function index(){
        $recetas = Receta::with('categoria','etiquetas','user')->get();
        return RecetaResource::collection($recetas);
    }

    public function store(){}

    public function show(Receta $receta){
        $receta = $receta->load('categoria','etiquetas','user');
        return new RecetaResource($receta);
    }

    public function update(){}

    public function destroy(){}

}
