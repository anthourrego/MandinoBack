<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnidadesCursosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unidades_cursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fk_unidad');
            $table->foreignId('fk_curso');
            $table->foreignId('fk_unidad_dependencia')->nullable();
            $table->boolean("estado")->default(1);
            $table->integer('orden')->length(11);
            $table->timestamps();

            $table->foreign('fk_unidad')->references('id')->on('unidades');
            $table->foreign('fk_curso')->references('id')->on('cursos');
            $table->foreign('fk_unidad_dependencia')->references('id')->on('unidades');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('unidades_cursos');
    }
}
