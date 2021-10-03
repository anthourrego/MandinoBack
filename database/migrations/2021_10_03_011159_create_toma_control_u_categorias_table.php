<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTomaControlUCategoriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('toma_control_u_categorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fk_toma_control');
            $table->foreignId('fk_categoria');
            $table->timestamps();

            $table->foreign('fk_toma_control')->references('id')->on('toma_control');
            $table->foreign('fk_categoria')->references('id')->on('toma_control_categorias');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('toma_control_u_categorias');
    }
}
