<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class PermisosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('permisos')->insert([
            [
                'id' => 1
                ,'nombre' => "escuelas"
                ,'tag' => 'Escuelas'
                ,'icono' => 'fas fa-school'
                ,'ruta' => 'escuelas'
                ,'fk_permiso' => NULL
                ,'created_at' => date('Y-m-d H:m:s')
                ,'updated_at' => date('Y-m-d H:m:s')
            ],[
                'id' => 2
                ,'nombre' => "localidades"
                ,'tag' => 'Localidades'
                ,'icono' => 'fas fa-globe-asia'
                ,'ruta' => NULL
                ,'fk_permiso' => NULL
                ,'created_at' => date('Y-m-d H:m:s')
                ,'updated_at' => date('Y-m-d H:m:s')
            ],[
                'id' => 3
                ,'nombre' => "paises"
                ,'tag' => 'Paises'
                ,'icono' => 'fas fa-flag'
                ,'ruta' => 'localidades/paises'
                ,'fk_permiso' => 2
                ,'created_at' => date('Y-m-d H:m:s')
                ,'updated_at' => date('Y-m-d H:m:s')
            ],[
                'id' => 4
                ,'nombre' => "estados"
                ,'tag' => 'Estados'
                ,'icono' => 'far fa-building'
                ,'ruta' => 'localidades/estados'
                ,'fk_permiso' => 2
                ,'created_at' => date('Y-m-d H:m:s')
                ,'updated_at' => date('Y-m-d H:m:s')
            ],[
                'id' => 5
                ,'nombre' => "ciudades"
                ,'tag' => 'Ciudades'
                ,'icono' => 'fas fa-city'
                ,'ruta' => 'localidades/ciudades'
                ,'fk_permiso' => 2
                ,'created_at' => date('Y-m-d H:m:s')
                ,'updated_at' => date('Y-m-d H:m:s')
            ],[
                'id' => 6
                ,'nombre' => "usuarios"
                ,'tag' => 'Usuarios'
                ,'icono' => 'fas fa-users'
                ,'ruta' => 'usuarios'
                ,'fk_permiso' => NULL
                ,'created_at' => date('Y-m-d H:m:s')
                ,'updated_at' => date('Y-m-d H:m:s')
            ]
        ]);
    }
}
