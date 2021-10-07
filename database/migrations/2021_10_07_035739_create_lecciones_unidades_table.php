<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeccionesUnidadesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lecciones_unidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fk_leccion');
            $table->foreignId('fk_unidad');
            $table->foreignId('fk_leccion_dependencia')->nullable();
            $table->boolean("estado")->default(1);
            $table->integer('orden')->length(11);
            $table->timestamps();

            $table->foreign('fk_leccion')->references('id')->on('lecciones');
            $table->foreign('fk_unidad')->references('id')->on('unidades');
            $table->foreign('fk_leccion_dependencia')->references('id')->on('lecciones');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lecciones_unidades');
    }
}
