<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IntroduccionEvaluacion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /* Schema::table('lecciones_progreso_usuarios', function (Blueprint $table) {
            $table->dateTime('fecha_completado')->nullable()->change();
            $table->integer('intentos_adicionales')->length(11)->nullable();
        }); */

        Schema::table('lecciones', function (Blueprint $table) {
            $table->integer('intentos_base')->length(11)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /* Schema::table('lecciones_progreso_usuarios', function (Blueprint $table) {
            $table->date('fecha_completado')->nullable()->change();
        }); */
    }
}
