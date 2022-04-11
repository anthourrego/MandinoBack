<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCertificadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('certificados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('fk_escuela');
            $table->foreignId('fk_unidad')->nullable();
            $table->foreignId('fk_curso')->nullable();
            $table->boolean("estado")->default(1);
            
            $table->foreign('fk_escuela')->references('id')->on('escuelas');
            $table->foreign('fk_unidad')->references('id')->on('unidades');
            $table->foreign('fk_curso')->references('id')->on('cursos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('certificados');
    }
}
