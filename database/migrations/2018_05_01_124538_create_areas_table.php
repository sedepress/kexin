<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAreasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id')->unsigned();
            $table->string('city_code',8)->default('')->comment('城市编码');
            $table->string('ad_code',16)->index()->default('')->comment('城市adcode');
            $table->integer('parent_id')->index()->default(0)->comment('上一级id');
            $table->string('name',32)->index()->default('')->comment('省城市名称');
            $table->double('longitude',15,8)->nullable()->comment('经度');
            $table->double('latitude',15,8)->nullable()->comment('纬度');
            $table->string('level',16)->index()->default('')->comment('地域级别');

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
        Schema::dropIfExists('areas');
    }
}
