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

    /**
     * @OA\Get(
     *    path="/api/recetas",
     *    summary="Consultar todas las recetas",
     *    description="Retorna todas las recetas",
     *    tags={"Recetas"},
     *    security={{"bearer_token":{}}},
     *    @OA\Response(
     *       response=200,
     *      description="Operación exitosa",
     *   ),
     *   @OA\Response(
     *     response=403,
     *     description="No autorizado"
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="No se encontraron recetas"
     *   ),
     *   @OA\Response(
     *    response=405,
     *    description="Método no permitido"
     *   )
     * )
     */

    public function index(){
        $this->authorize('Ver recetas');

        $recetas = Receta::with('categoria','etiquetas','user')->get();
        return RecetaResource::collection($recetas);
    }
    
 
    /**
     * @OA\Post(
     *    path="/api/recetas",
     *    summary="Crear receta",
     *    description="Crear una nueva receta",
     *    tags={"Recetas"},
     *    security={{"bearer_token":{}}},
     *    @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              required={"categoria_id","titulo","descripcion","ingredientes","instrucciones","imagen","etiquetas"},
     *              @OA\Property(property="categoria_id", type="integer", example="1"),
     *              @OA\Property(property="titulo", type="string", example="Receta 1"),
     *              @OA\Property(property="descripcion", type="string", example="descripcion de la receta"),
     *              @OA\Property(property="ingredientes", type="string", example="Preparación de la receta"),
     *              @OA\Property(property="instrucciones", type="string", example="instrucciones de la receta"),
     *              @OA\Property(property="imagen", type="string", format="binary"),
     *              @OA\Property(property="etiquetas", type="string", example="[1,2,3]")
     *         )
     *       )
     *    ),
     *    @OA\Response(
     *       response=201,
     *       description="Receta creada",
     *    ),
     *    @OA\Response(
     *       response=403,
     *       description="No autorizado"
     *    )
     * )
     */

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

    /**
     * @OA\Get(
     *     path="/api/recetas/{receta}",
     *     summary="Obtener receta por ID",
     *     description="Retorna una receta con su categoría, etiquetas y usuario asociados.",
     *     tags={"Recetas"},
     *     security={ {"bearer_token": {} }},
     *     @OA\Parameter(
     *        name="receta",
     *        description="ID de la receta",
     *        required=true,
     *        in="path",
     *        @OA\Schema(
     *           type="integer"
     *        )
     *     ),
     *     @OA\Response(
     *        response=200,
     *        description="Receta",
     *     ),
     *     @OA\Response(
     *        response=403,
     *        description="No autorizado",
     *     )
     * )
     */

    public function show(Receta $receta){
        $this->authorize('Ver recetas');

        $receta = $receta->load('categoria','etiquetas','user');
        return new RecetaResource($receta);
    }

    /**
     * @OA\Put(
     *    path="/api/recetas/{receta}",
     *    summary="Actualizar receta",
     *    description="Actualizar una receta por su ID",
     *    tags={"Recetas"},
     *    security={{"bearer_token": {}}},
     *    @OA\Parameter(
     *        name="receta",
     *        description="ID de la receta",
     *        required=true,
     *        in="path",
     *        @OA\Schema(
     *           type="integer"
     *        )
     *     ),
     *    @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *               required={"categoria_id","titulo","descripcion","ingredientes","instrucciones","etiquetas"},
     *               @OA\Property(property="categoria_id", type="integer", example="1"),
     *               @OA\Property(property="titulo", type="string", example="Receta 1"),
     *               @OA\Property(property="descripcion", type="string", example="Descripción de la receta"),
     *               @OA\Property(property="ingredientes", type="string", example="Ingredientes de la receta"),
     *               @OA\Property(property="instrucciones", type="string", example="Instrucciones de la receta"),
     *               @OA\Property(property="etiquetas", type="string", example="[1,2,3]")
     *           )
     *       )
     *    ),
     *    @OA\Response(
     *       response=202,
     *       description="Receta actualizada",
     *    ),
     *    @OA\Response(
     *       response=403,
     *       description="No autorizado"
     *    ),
     *    @OA\Response(
     *       response=404,
     *       description="Receta no encontrada"
     *    )
     * )
     */
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

/**
    * @OA\Delete(
    *    path="/api/recetas/{receta}",
    *    summary="Eliminar receta",
    *    description="Elimina una receta por su ID.",
    *    tags={"Recetas"},
    *    security={{"bearer_token": {}}},
    *    @OA\Parameter(
    *        name="receta",
    *        description="ID de la receta",
    *        required=true,
    *        in="path",
    *        @OA\Schema(
    *           type="integer"
    *        )
    *     ),
    *    @OA\Response(
    *       response=204,
    *       description="Receta eliminada con éxito",
    *    ),
    *    @OA\Response(
    *       response=403,
    *       description="No autorizado",
    *    ),
    *    @OA\Response(
    *       response=404,
    *       description="Receta no encontrada"
    *    )
    * )
*/

    public function destroy(Receta $receta){
        //$this->authorize('Eliminar recetas');
        
        //$this->authorize('delete', $receta);

        $receta->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT); // 204 No Content
    }

}

