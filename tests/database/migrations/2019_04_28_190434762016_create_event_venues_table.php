<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventVenuesTable extends Migration
{
    public function up()
    {
        Schema::create('event_venues', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('created_by')->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('website')->nullable();
            $table->integer('continent_id');
            $table->integer('country_id');
            $table->integer('region_id')->nullable();
            //$table->string('state_province')->nullable();
            $table->string('city');
            $table->string('address')->nullable();
            $table->text('extra_info')->nullable();
            $table->string('zip_code')->nullable();
            $table->float('lat', 8, 6)->nullable();
            $table->float('lng', 9, 6)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('event_venues');
    }
}
