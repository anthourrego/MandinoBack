<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTomaControlCategoriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('toma_control_categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 30);
            $table->boolean('estado')->default(1);
            $table->timestamps();
        });

        Schema::table('permisos_sistema', function (Blueprint $table) {
            $table->foreignId('fk_categorias_toma_control')->nullable();

            $table->foreign('fk_categorias_toma_control')->references('id')->on('toma_control_categorias');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {   
        if (Schema::hasColumn('permisos_sistema', 'fk_categorias_toma_control')){
            Schema::table('permisos_sistema', function (Blueprint $table) {
                $table->dropForeign(['fk_categorias_toma_control']);
            });
        }
        
        Schema::dropIfExists('toma_control_categorias');
    }
}
