<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGovernmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('governments', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name');
            $table->string('url',128)->comment('网址');
            $table->string('image_url',128)->default('')->comment('图标地址');
            $table->integer('area_id')->default(1)->comment('区域id');
            $table->integer('order')->unsigned()->commit('排列顺序');
            $table->boolean('status')->default(true)->comment('是否生效');
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
        Schema::dropIfExists('governments');
    }
}
