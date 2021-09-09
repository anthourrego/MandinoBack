<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class PerfilesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){
        DB::table('perfiles')->insert([
            [
                "nombre" => 'Pruebas'
                ,"created_at" => date('Y-m-d H:m:s')
                ,"updated_at" => date('Y-m-d H:m:s')
            ]
        ]);
    }
}
