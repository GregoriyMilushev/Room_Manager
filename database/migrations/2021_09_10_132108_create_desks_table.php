<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDesksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('desks', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('price_per_week', 5, 2);
            $table->enum('size', ['small', 'big']);
            $table->string('position');
            $table->boolean('is_taken')->default(false);
            $table->integer('user_id')->unsigned()->nullable();
            $table->unsignedBigInteger('rented_weeks')->nullable();
            $table->date('rented_at')->nullable();
            $table->date('rent_until')->nullable();
            $table->integer('room_id')->unsigned();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('room_id')->references('id')->on('rooms');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
     public function down()
     {
         Schema::dropIfExists('desks');
     }
 }
