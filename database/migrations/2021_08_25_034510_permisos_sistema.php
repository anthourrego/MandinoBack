<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PermisosSistema extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permisos_sistema', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fk_usuario')->nullable();
            $table->foreignId('fk_perfil')->nullable();
            $table->foreignId('fk_permiso')->nullable();
            $table->foreignId('fk_escuelas')->nullable();
            $table->integer('tipo');
            $table->boolean('estado')->default(1);        
            $table->timestamps();

            $table->foreign('fk_usuario')->references('id')->on('users');
            $table->foreign('fk_perfil')->references('id')->on('perfiles');
            $table->foreign('fk_permiso')->references('id')->on('permisos');
            $table->foreign('fk_escuelas')->references('id')->on('escuelas');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permisos_sistema');
    }
}
