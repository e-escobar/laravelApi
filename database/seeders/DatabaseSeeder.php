<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Receta;
use App\Models\Categoria;
use App\Models\Etiqueta;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolSeeder::class);

        User::factory()->create([
            'name' => 'Eric Escobar Mendoza',
            'email' => 'eescobar@admin.com',
        ])->assignRole('Administrador');

        User::factory()->create([
            'name' => 'Iraic Alcantar Alcantar',
            'email' => 'iraic@gmail.com',
        ])->assignRole('Editor');
        
        User::factory(5)->create()->each(function ($user) {
            $user->assignRole('Usuario');
        });
     
        Categoria::factory(10)->create();
        Receta::factory(10)->create();
        Etiqueta::factory(10)->create();


        // Relación de muchos a muchos
        $recetas = Receta::all();
        $etiquetas = Etiqueta::all();

        foreach ($recetas as $receta) {
            $receta->etiquetas()->attach($etiquetas->random(rand(2, 4)));
        }
        

    }
}
