<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paises', function (Blueprint $table) {
            $table->id();
            $table->string("name", 100);
            $table->char('iso3', 3)->nullable();
            $table->char('numeric_code', 3)->nullable();
            $table->char('iso2', 2)->nullable();
            $table->string("phone_code", 255)->nullable();
            $table->string("capital", 255)->nullable();
            $table->string("currency", 255)->nullable();
            $table->string("currency_symbol", 255)->nullable();
            $table->string("tld", 255)->nullable();
            $table->string("native", 255)->nullable();
            $table->string("region", 255)->nullable();
            $table->string("subregion", 255)->nullable();
            $table->text("timezones");
            $table->text("translations");
            $table->decimal('latitude', $precision = 10, $scale = 8)->nullable();
            $table->decimal('longitude', $precision = 11, $scale = 8)->nullable();
            $table->string("emoji", 191)->nullable();
            $table->string("emojiU", 191)->nullable();
            $table->boolean("flag")->default(1);
            $table->string("wikiDataId", 255)->nullable();
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
        Schema::dropIfExists('paises');
    }
}
