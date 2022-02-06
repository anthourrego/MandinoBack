<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTomaControlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('toma_controls', function (Blueprint $table) {
            $table->id();
            $table->text('nombre');
            $table->longText('descripcion')->nullable();
            $table->text('poster')->nullable();
            $table->text('ruta');
            $table->boolean('visibilidad')->default(1);
            $table->boolean('comentarios')->default(1);
            $table->boolean('estado')->default(1);
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
        Schema::dropIfExists('toma_controls');
    }
}
