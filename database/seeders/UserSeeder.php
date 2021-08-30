<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'id' => 0
            ,'nro_documento' => "0000"
            ,'usuario' => 'admin'
            ,'password' => '$2y$15$Pbu7Qq9uCceP7wAJ/ZGW3uaJklO89Xk5.ZzKeFfJ63NPdUvbPzxaO'
            ,'nombre1' => 'admin'
            ,'apellido1' => 'admin'
            ,'email' => 'admin@admin.com'
            ,'created_at' => date('Y-m-d H:m:s')
            ,'updated_at' => date('Y-m-d H:m:s')
            ,'fk_municipio' => 21195
        ]);
    }
}
