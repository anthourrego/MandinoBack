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
                ,'nombre' => "administrativo"
                ,'tag' => 'Administrativo'
                ,'icono' => 'fas fa-tachometer-alt'
                ,'ruta' => 'administrativo'
                ,'fk_permiso' => NULL
                ,'created_at' => date('Y-m-d H:m:s')
                ,'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'id' => 2
                ,'nombre' => "universidad"
                ,'tag' => 'Universidad'
                ,'icono' => 'fas fa-school'
                ,'ruta' => 'universidad'
                ,'fk_permiso' => NULL
                ,'created_at' => date('Y-m-d H:m:s')
                ,'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'id' => 3
                ,'nombre' => "toma-el-control"
                ,'tag' => 'Toma el control'
                ,'icono' => 'fas fa-film'
                ,'ruta' => 'toma-el-control'
                ,'fk_permiso' => NULL
                ,'created_at' => date('Y-m-d H:m:s')
                ,'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'id' => 4
                ,'nombre' => "escuelas"
                ,'tag' => 'Escuelas'
                ,'icono' => 'fas fa-school'
                ,'ruta' => 'escuelas'
                ,'fk_permiso' => 1
                ,'created_at' => date('Y-m-d H:m:s')
                ,'updated_at' => date('Y-m-d H:m:s')
            ],[
                'id' => 5
                ,'nombre' => "localidades"
                ,'tag' => 'Localidades'
                ,'icono' => 'fas fa-globe-asia'
                ,'ruta' => NULL
                ,'fk_permiso' => 1
                ,'created_at' => date('Y-m-d H:m:s')
                ,'updated_at' => date('Y-m-d H:m:s')
            ],[
                'id' => 6
                ,'nombre' => "paises"
                ,'tag' => 'Paises'
                ,'icono' => 'fas fa-flag'
                ,'ruta' => 'localidades/paises'
                ,'fk_permiso' => 5
                ,'created_at' => date('Y-m-d H:m:s')
                ,'updated_at' => date('Y-m-d H:m:s')
            ],[
                'id' => 7
                ,'nombre' => "estados"
                ,'tag' => 'Estados'
                ,'icono' => 'far fa-building'
                ,'ruta' => 'localidades/estados'
                ,'fk_permiso' => 5
                ,'created_at' => date('Y-m-d H:m:s')
                ,'updated_at' => date('Y-m-d H:m:s')
            ],[
                'id' => 8
                ,'nombre' => "ciudades"
                ,'tag' => 'Ciudades'
                ,'icono' => 'fas fa-city'
                ,'ruta' => 'localidades/ciudades'
                ,'fk_permiso' => 5
                ,'created_at' => date('Y-m-d H:m:s')
                ,'updated_at' => date('Y-m-d H:m:s')
            ],[
                'id' => 9
                ,'nombre' => "usuarios"
                ,'tag' => 'Usuarios'
                ,'icono' => 'fas fa-users'
                ,'ruta' => 'usuarios'
                ,'fk_permiso' => 1
                ,'created_at' => date('Y-m-d H:m:s')
                ,'updated_at' => date('Y-m-d H:m:s')
            ],[
                'id' => 10
                ,'nombre' => "permisos"
                ,'tag' => 'Permisos'
                ,'icono' => 'fas fa-lock'
                ,'ruta' => 'permisos'
                ,'fk_permiso' => 1
                ,'created_at' => date('Y-m-d H:m:s')
                ,'updated_at' => date('Y-m-d H:m:s')
            ]
        ]);
    }
}
