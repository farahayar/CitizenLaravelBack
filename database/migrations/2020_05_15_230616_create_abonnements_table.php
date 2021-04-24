<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAbonnementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('abonnement', function (Blueprint $table) {
            $table->collation = 'utf8_general_ci';
            $table->charset = 'utf8';
            $table->engine= 'InnoDB';
            $table->increments('id');
            $table->integer('abonne_id')->unsigned();
            $table->integer('suivi_id')->unsigned();

            $table->foreign('abonne_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('suivi_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('abonnement');
    }
}
