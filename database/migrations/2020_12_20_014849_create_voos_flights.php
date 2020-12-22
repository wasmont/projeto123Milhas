<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoosFlights extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('voos_flights', function (Blueprint $table) {
            $table->id();
            $table->text('cia'); 		
            $table->string('fare',100); 		
            $table->string('flightNumber',100);
            $table->date('departureDate');
            $table->date('arrivalDate'); 
            $table->time('departureTime', $precision = 0); 
            $table->time('arrivalTime', $precision = 0); 
            $table->string('origin',20); 		
            $table->string('destination',20); 
            $table->decimal('price', $precision = 10, $scale = 2);
            $table->integer('outbound');	
            $table->integer('inbound'); 	
            $table->integer('classService'); 	
            $table->decimal('tax', $precision = 10, $scale = 2);
            $table->string('duration',20); 	
            $table->timestamps();
            $table->engine = 'InnoDB';
            $table->collation = 'utf8mb4_unicode_ci';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('voos_flights');
    }
}
