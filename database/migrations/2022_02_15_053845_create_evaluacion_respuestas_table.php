<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluacionRespuestasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evaluacion_respuestas', function (Blueprint $table) {
            $table->id();
            $table->foreignId("fk_intento_leccion");
            $table->foreignId("fk_pregunta_respuesta");
            $table->timestamps();
            $table->foreign('fk_intento_leccion')->references('id')->on('intento_leccion_usuario');
            $table->foreign('fk_pregunta_respuesta')->references('id')->on('evaluacion_preguntas_opciones');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('evaluacion_respuestas');
    }
}
