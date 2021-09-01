<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMunicipiosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('municipios', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('state_code', 255);
            $table->mediumInteger('country_id');
            $table->char('country_code', 2);
            $table->decimal('latitude', $precision = 10, $scale = 8)->nullable();
            $table->decimal('longitude', $precision = 11, $scale = 8)->nullable();
            $table->foreignId('state_id');
            $table->boolean("flag")->default(1);
            $table->timestamps();
            $table->foreign('state_id')->references('id')->on('departamentos');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('fk_municipio');

            $table->foreign('fk_municipio')->references('id')->on('municipios');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['fk_municipio']);
        });

        Schema::dropIfExists('municipios');
    }
}
