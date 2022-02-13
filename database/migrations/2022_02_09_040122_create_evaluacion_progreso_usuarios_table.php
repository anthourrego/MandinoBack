<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluacionProgresoUsuariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('intento_leccion_usuario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fk_user');
            $table->foreignId('fk_leccion');
            $table->integer('num_preguntas_correctas')->default(0);
            $table->integer('num_preguntas_totales')->default(0);
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_final');
            $table->string('captura_pantalla')->nullable();
            $table->timestamps();
            $table->foreign('fk_user')->references('id')->on('users');
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
        Schema::dropIfExists('evaluacion_progreso_usuarios');
    }
}
