<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTomaControlMeGustaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('toma_control_me_gusta', function (Blueprint $table) {
            $table->id();
            $table->boolean('me_gusta');
            $table->foreignId('fk_user');
            $table->foreignId('fk_toma_control');
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
        Schema::dropIfExists('toma_control_me_gusta');
    }
}
