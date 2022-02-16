<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluacionPreguntasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evaluacion_preguntas', function (Blueprint $table) {
            $table->id();
            $table->longText('pregunta');
            $table->foreignId('fk_leccion');
            $table->integer('tipo_pregunta')->length(11);
            $table->timestamps();
            $table->foreign('fk_leccion')->references('id')->on('lecciones');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('evaluacion_preguntas');
    }
}
