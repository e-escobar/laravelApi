<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Resources\EtiquetaResource;

use App\Models\Etiqueta;

class EtiquetaController extends Controller
{
    public function index(){
        return EtiquetaResource::collection(Etiqueta::with('recetas')->get());
    }

    public function show(Etiqueta $etiqueta){
        return new EtiquetaResource($etiqueta->load('recetas'));
    }
}
