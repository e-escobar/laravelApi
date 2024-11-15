<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Http\Resources\RecetaResource;

use App\Http\Requests\StoreRecetasRequest;
use App\Http\Requests\UpdateRecetasRequest;

use Symfony\Component\HttpFoundation\Response;

use App\Models\Receta;

class RecetaController extends Controller
{
    use AuthorizesRequests;  

    public function index(){
        $this->authorize('Ver recetas');

        $recetas = Receta::with('categoria','etiquetas','user')->get();
        return RecetaResource::collection($recetas);
    }

    public function store(StoreRecetasRequest $request){
        $this->authorize('Crear recetas');

        //$receta = Receta::create($request->all());
        $receta = $request->user()->recetas()->create($request->all());
        $receta->etiquetas()->attach(json_decode($request->etiquetas));

        // Almacenar la imagen en el servidor
        $receta->imagen = $request->file('imagen')->store('recetas','public');
        $receta->save();

        return response()->json(new RecetaResource($receta), 
                                Response::HTTP_CREATED); // 201 Created
    }

    public function show(Receta $receta){
        $this->authorize('Ver recetas');

        $receta = $receta->load('categoria','etiquetas','user');
        return new RecetaResource($receta);
    }

    public function update(UpdateRecetasRequest $request, Receta $receta){
        $this->authorize('Editar recetas');

        $this->authorize('update', $receta);

        $receta->update($request->all());

        if($etiquetas = json_decode($request->etiquetas)){
            $receta->etiquetas()->sync($etiquetas);
        }

        // Modificar la imagen en el servidor si se envía una nueva imagen
        if($request->file('imagen')){
            $receta->imagen = $request->file('imagen')->store('recetas','public');
            $receta->save();
        }

        return response()->json(new RecetaResource($receta), 
                                Response::HTTP_ACCEPTED); // 202 Accepted
    }

    public function destroy(Receta $receta){
        //$this->authorize('Eliminar recetas');
        
        //$this->authorize('delete', $receta);

        $receta->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT); // 204 No Content
    }

}
