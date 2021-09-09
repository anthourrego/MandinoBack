<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call(PaisesSeeder::class);
        $this->call(DepartamentosSeeder::class);
        $this->call(MunicipiosSeeder::class);
        $this->call(MunicipiosSeeder2::class);
        $this->call(MunicipiosSeeder3::class);
        $this->call(PermisosSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(PermisosSistemaSeeder::class);
    }
}
