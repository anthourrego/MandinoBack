<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMunicipiosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('municipios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 30);
            $table->foreignId('fk_departamento');
            $table->boolean('estado', 1)->default(1);
            $table->timestamps();

            $table->foreign('fk_departamento')->references('id')->on('departamentos');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('fk_municipio');

            $table->foreign('fk_municipio')->references('id')->on('municipios');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['fk_municipio']);
        });

        Schema::dropIfExists('municipios');
    }
}
