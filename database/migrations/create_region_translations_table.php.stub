<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegionTranslationsTable extends Migration
{
    public function up()
    {
        Schema::create('region_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('region_id')->unsigned();
            $table->string('name');
            $table->string('slug');
            $table->string('locale')->index();

            $table->unique(['region_id', 'locale']);
            $table->foreign('region_id')->references('id')->on('regions')->onDelete('cascade');
        
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('region_translations');
    }
}
