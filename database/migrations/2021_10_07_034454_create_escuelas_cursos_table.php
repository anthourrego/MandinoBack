<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEscuelasCursosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('escuelas_cursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fk_curso');
            $table->foreignId('fk_escuela');
            $table->foreignId('fk_curso_dependencia')->nullable();
            $table->boolean("estado")->default(1);
            $table->integer('orden')->length(11);
            $table->timestamps();

            $table->foreign('fk_curso')->references('id')->on('cursos');
            $table->foreign('fk_escuela')->references('id')->on('escuelas');
            $table->foreign('fk_curso_dependencia')->references('id')->on('cursos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('escuelas_cursos');
    }
}
