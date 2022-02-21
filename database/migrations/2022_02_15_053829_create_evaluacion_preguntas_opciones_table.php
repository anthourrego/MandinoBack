<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluacionPreguntasOpcionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evaluacion_preguntas_opciones', function (Blueprint $table) {
            $table->id();
            $table->longText('descripcion');
            $table->boolean("correcta")->default(1);
            $table->foreignId("fk_pregunta");
            $table->timestamps();
            $table->foreign('fk_pregunta')->references('id')->on('evaluacion_preguntas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('evaluacion_preguntas_opciones');
    }
}
