<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Resources\RecetaResource;
use App\Http\Requests\StoreRecetasRequest;
use Symfony\Component\HttpFoundation\Response;

use App\Http\Requests\UpdateRecetasRequest;

use App\Models\Receta;

class RecetaController extends Controller
{
    public function index(){
        $recetas = Receta::with('categoria','etiquetas','user')->get();
        return RecetaResource::collection($recetas);
    }

    public function store(StoreRecetasRequest $request){
        //$receta = Receta::create($request->all());
        $receta = $request->user()->recetas()->create($request->all());
        $receta->etiquetas()->attach(json_decode($request->etiquetas));

        return response()->json(new RecetaResource($receta), 
                                Response::HTTP_CREATED); // 201 Created
    }

    public function show(Receta $receta){
        $receta = $receta->load('categoria','etiquetas','user');
        return new RecetaResource($receta);
    }

    public function update(UpdateRecetasRequest $request, Receta $receta){
        $receta->update($request->all());

        if($etiquetas = json_decode($request->etiquetas)){
            $receta->etiquetas()->sync($etiquetas);
        }

        return response()->json(new RecetaResource($receta), 
                                Response::HTTP_ACCEPTED); // 202 Accepted
    }

    public function destroy(Receta $receta){
        $receta->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT); // 204 No Content
    }

}
