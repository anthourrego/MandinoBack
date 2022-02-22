<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyLeccionesProgresoUsuarios extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('lecciones_progreso_usuarios', function (Blueprint $table) {
            $table->integer('porcentaje_ganar')->length(3)->nullable();
        });

        Schema::table('lecciones', function (Blueprint $table) {
            $table->longText('tiempo_intento')->nullable();
        });

        Schema::table('lecciones_unidades', function (Blueprint $table) {
            $table->integer('tiempo_dependencia')->length(11)->nullable();
        });

        Schema::table('unidades_cursos', function (Blueprint $table) {
            $table->integer('tiempo_dependencia')->length(11)->nullable();
        });

        Schema::table('escuelas_cursos', function (Blueprint $table) {
            $table->integer('tiempo_dependencia')->length(11)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        //
    }
}
