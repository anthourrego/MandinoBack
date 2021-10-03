<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTomaControlComentariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('toma_control_comentarios', function (Blueprint $table) {
            $table->id();
            $table->longText('comentario');
            $table->foreignId('fk_toma_control');
            $table->boolean('estado');
            $table->boolean('visibilidad');
            $table->foreignId('fk_user');
            $table->unsignedBigInteger('fk_comentario')->nullable();
            $table->timestamps();

            $table->foreign('fk_user')->references('id')->on('users');
            $table->foreign('fk_toma_control')->references('id')->on('toma_control');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('toma_control_comentarios');
    }
}
