<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class DepartamentosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        DB::table('departamentos')->insert([
            [
                'id' => 5,
                'nombre' => 'ANTIOQUIA',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 8,
                'nombre' => 'ATLÁNTICO',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 11,
                'nombre' => 'BOGOTÁ, D.C.',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 13,
                'nombre' => 'BOLÍVAR',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 15,
                'nombre' => 'BOYACÁ',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 17,
                'nombre' => 'CALDAS',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 18,
                'nombre' => 'CAQUETÁ',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 19,
                'nombre' => 'CAUCA',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 20,
                'nombre' => 'CESAR',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 23,
                'nombre' => 'CÓRDOBA',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 25,
                'nombre' => 'CUNDINAMARCA',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 27,
                'nombre' => 'CHOCÓ',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 41,
                'nombre' => 'HUILA',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 44,
                'nombre' => 'LA GUAJIRA',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 47,
                'nombre' => 'LA MAGDALENA',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 50,
                'nombre' => 'META',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 52,
                'nombre' => 'NARIÑO',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 54,
                'nombre' => 'NORTE DE SANTANDER',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 63,
                'nombre' => 'QUINDIO',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 66,
                'nombre' => 'RISARALDA',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 68,
                'nombre' => 'SANTANDER',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 70,
                'nombre' => 'SUCRE',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 73,
                'nombre' => 'TOLIMA',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 76,
                'nombre' => 'VALLE DEL CAUCA',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 81,
                'nombre' => 'ARAUCA',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 85,
                'nombre' => 'CASANARE',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 86,
                'nombre' => 'PUTUMAYO',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 88,
                'nombre' => 'ARCHIPIÉLAGO DE SAN ANDRÉS, PROVIDENCIA Y SANTA CATALINA',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 91,
                'nombre' => 'AMAZONAS',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 94,
                'nombre' => 'GUAINÍA',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 95,
                'nombre' => 'GUAVIARE',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 97,
                'nombre' => 'VAUPÉS',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ],
            [
                'id' => 99,
                'nombre' => 'VICHADA',
                'estado' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ]
        ]);
    }
}
