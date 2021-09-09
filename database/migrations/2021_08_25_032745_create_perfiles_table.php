<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePerfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('perfiles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 30);
            $table->boolean('estado')->default(1);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('fk_perfil')->nullable();

            $table->foreign('fk_perfil')->references('id')->on('perfiles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        if (Schema::hasColumn('users', 'fk_perfil')){
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['fk_perfil']);
            });
        }

        Schema::dropIfExists('perfiles');
    }
}
