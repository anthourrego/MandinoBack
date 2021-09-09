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
            'id' => 1
            ,'nro_documento' => "0000"
            ,'usuario' => 'admin'
            ,'password' => '$2y$15$Ui5AQTHCS4pkuOFyOAq61OlsjnQdx9rWzmXPDHtol9B0lHj34pU/i'
            ,'nombre1' => 'admin'
            ,'apellido1' => 'admin'
            ,'email' => 'admin@admin.com'
            ,'created_at' => date('Y-m-d H:m:s')
            ,'updated_at' => date('Y-m-d H:m:s')
            ,'fk_municipio' => 21195
        ]);
    }
}
