<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

use App\Models\Receta;
use App\Models\User;
use App\Models\Categoria;
use App\Models\Etiqueta;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);
uses(WithFaker::class);

test('index', function () {
    $this->artisan('db:seed', ['--class' => 'RolSeeder']);

    Sanctum::actingAs(User::factory()->create()->assignRole('Usuario'));

    Categoria::factory()->create();
    Receta::factory(3)->create();

    $response = $this->getJson('/api/recetas');
    //dd($response->json());

    $response->assertStatus(Response::HTTP_OK)
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'tipo',
                    'atributos' => [
                        'categoria',
                        'autor',
                    ]
                ]
            ]
        ]);
});


test('show', function () {
    $this->artisan('db:seed', ['--class' => 'RolSeeder']);

    Sanctum::actingAs(User::factory()->create()->assignRole('Usuario'));

    $categoria = Categoria::factory()->create();
    $receta = Receta::factory()->create();

    $response = $this->getJson("/api/recetas/{$receta->id}");
    //dd($response->json());

    $response->assertStatus(Response::HTTP_OK)
        ->assertJsonStructure([
            'data' => [
                'id',
                'tipo',
                'atributos' => [
                    'categoria',
                    'autor',
                ]
            ]
        ]);
});

test('store', function () {
    // Ejecuta el seeder de roles
    $this->artisan('db:seed', ['--class' => 'RolSeeder']);

    $usuario = Sanctum::actingAs(User::factory()->create()->assignRole('Administrador'));

    $categoria = Categoria::factory()->create();
    $etiqueta = Etiqueta::factory()->create();

    $data = [
        'categoria_id' => $categoria->id,
        'titulo' => $this->faker->sentence,        
        'descripcion' => $this->faker->sentence,
        'ingredientes' => $this->faker->sentence,
        'instrucciones' => $this->faker->sentence,
        'imagen' => UploadedFile::fake()->image('receta.png'),
        'etiquetas' => $etiqueta->id,
    ];

    $response = $this->postJson('/api/recetas/', $data);
    //dd($response->json());

    $response->assertStatus(Response::HTTP_CREATED);

    // Verificar que se haya creado el registro
    $this->assertDatabaseHas('recetas', ['titulo' => $response['atributos']['titulo']]);
});

test('update', function () {
    // Ejecuta el seeder de roles
    $this->artisan('db:seed', ['--class' => 'RolSeeder']);

    $usuario = Sanctum::actingAs(User::factory()->create()->assignRole('Administrador'));

    $categoria = Categoria::factory()->create();
    $receta = Receta::factory()->create();

    $data = [
        'categoria_id' => $categoria->id,
        'titulo' => 'titulo actualizado',      
        'descripcion' => 'descripcion actualizada',
        'ingredientes' => $this->faker->sentence,
        'instrucciones' => $this->faker->sentence,
    ];

    $response = $this->putJson("/api/recetas/{$receta->id}", $data);
    //dd($response->json());

    $response->assertStatus(Response::HTTP_ACCEPTED);

    // Verificar que se haya actualizado el registro
    $this->assertDatabaseHas('recetas', [
        'titulo' => 'titulo actualizado',
        'descripcion' => 'descripcion actualizada',
    ]);
});


test('destroy', function () {
    $this->artisan('db:seed', ['--class' => 'RolSeeder']);

    Sanctum::actingAs(User::factory()->create()->assignRole('Administrador'));

    Categoria::factory()->create();

    $receta = Receta::factory()->create();

    $response = $this->deleteJson("/api/recetas/{$receta->id}");

    $response->assertStatus(Response::HTTP_NO_CONTENT);

    // Verificar que se haya eliminado el registro
    $this->assertDatabaseMissing('recetas', ['id' => $receta->id]);
});

test('destroy_editor', function () {
    $this->artisan('db:seed', ['--class' => 'RolSeeder']);

    Sanctum::actingAs(User::factory()->create()->assignRole('Editor'));

    Categoria::factory()->create();

    $receta = Receta::factory()->create();

    $response = $this->deleteJson("/api/recetas/{$receta->id}");

    $response->assertStatus(Response::HTTP_FORBIDDEN);
});