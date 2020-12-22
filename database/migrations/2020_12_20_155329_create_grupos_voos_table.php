<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGruposVoosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grupos_voos', function (Blueprint $table) {
            $table->increments('id', true);
            $table->bigInteger('codigo_voo'); 
            $table->integer('grupo')->nullable(false);
            $table->engine = 'InnoDB';
            $table->collation = 'utf8mb4_unicode_ci';
        });

        // Schema::table('grupos_voos', function($table) {
        //     $table->foreign('codigo_voo')->references('id')->on('voos_flights'); 
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('grupos_voos');
    }
}
