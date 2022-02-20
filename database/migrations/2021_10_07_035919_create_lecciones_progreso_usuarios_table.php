<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeccionesProgresoUsuariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lecciones_progreso_usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fk_user');
            $table->foreignId('fk_leccion');
            $table->date('fecha_completado')->nullable();
            $table->integer('tiempo_video')->length(10)->nullable();
            $table->foreign('fk_user')->references('id')->on('users');
            $table->foreign('fk_leccion')->references('id')->on('lecciones');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::dropIfExists('lecciones_progreso_usuarios');
    }
}
