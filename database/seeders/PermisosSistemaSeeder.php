<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class PermisosSistemaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        DB::table('permisos_sistema')->insert([
            [
                "fk_usuario" => 1
                ,"fk_permiso" => 1
                ,"tipo" => '0'
                ,'created_at' => date('Y-m-d H:m:s')
                ,'updated_at' => date('Y-m-d H:m:s')
            ],[
                "fk_usuario" => 1
                ,"fk_permiso" => 2
                ,"tipo" => '0'
                ,'created_at' => date('Y-m-d H:m:s')
                ,'updated_at' => date('Y-m-d H:m:s')
            ],[
                "fk_usuario" => 1
                ,"fk_permiso" => 3
                ,"tipo" => '0'
                ,'created_at' => date('Y-m-d H:m:s')
                ,'updated_at' => date('Y-m-d H:m:s')
            ],[
                "fk_usuario" => 1
                ,"fk_permiso" => 4
                ,"tipo" => '0'
                ,'created_at' => date('Y-m-d H:m:s')
                ,'updated_at' => date('Y-m-d H:m:s')
            ],[
                "fk_usuario" => 1
                ,"fk_permiso" => 5
                ,"tipo" => '0'
                ,'created_at' => date('Y-m-d H:m:s')
                ,'updated_at' => date('Y-m-d H:m:s')
            ],[
                "fk_usuario" => 1
                ,"fk_permiso" => 6
                ,"tipo" => '0'
                ,'created_at' => date('Y-m-d H:m:s')
                ,'updated_at' => date('Y-m-d H:m:s')
            ],[
                "fk_usuario" => 1
                ,"fk_permiso" => 7
                ,"tipo" => '0'
                ,'created_at' => date('Y-m-d H:m:s')
                ,'updated_at' => date('Y-m-d H:m:s')
            ],[
                "fk_usuario" => 1
                ,"fk_permiso" => 8
                ,"tipo" => '0'
                ,'created_at' => date('Y-m-d H:m:s')
                ,'updated_at' => date('Y-m-d H:m:s')
            ],[
                "fk_usuario" => 1
                ,"fk_permiso" => 9
                ,"tipo" => '0'
                ,'created_at' => date('Y-m-d H:m:s')
                ,'updated_at' => date('Y-m-d H:m:s')
            ],[
                "fk_usuario" => 1
                ,"fk_permiso" => 10
                ,"tipo" => '0'
                ,'created_at' => date('Y-m-d H:m:s')
                ,'updated_at' => date('Y-m-d H:m:s')
            ]
        ]);
    }
}
