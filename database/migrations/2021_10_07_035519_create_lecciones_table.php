<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeccionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lecciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->longText('contenido')->nullable();
            $table->longText('url_contenido')->nullable();
            $table->boolean("estado")->default(1);
            $table->integer('orden')->length(11);
            $table->boolean("tipo")->default(1);
            $table->timestamps();
            $table->integer("porcentaje_ganar")->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lecciones');
    }
}
