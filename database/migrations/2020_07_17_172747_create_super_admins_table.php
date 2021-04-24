<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuperAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('super_admins', function (Blueprint $table) {
            $table->collation = 'utf8_general_ci';
            $table->charset = 'utf8';
            $table->engine= 'InnoDB';
            $table->increments('id');
            $table->string('nom');
            $table->string('prenom');
            $table->date('dateNaiss');
            $table->string('adresse');
            $table->string('cin');
            $table->string('tel');
            $table->string('img');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('motDePass');
            $table->rememberToken();
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
        Schema::dropIfExists('super_admins');
    }
}
