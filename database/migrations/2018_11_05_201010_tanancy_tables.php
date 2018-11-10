<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TanancyTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenancies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('route');
            $table->timestamps();
        });

        Schema::create('tenancy_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('role');
            $table->integer('user_id')->unsigned();
            $table->integer('tenancy_id')->unsigned();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('tenancy_id')->references('id')->on('tenancies');
        });

        Schema::create('rentables', function (Blueprint $table) {
            $table->integer('tenancy_id')->unsigned();
            $table->integer('rentables_id')->unsigned();
            $table->integer('rentables_type')->unsigned();

            $table->foreign('tenancy_id')->references('id')->on('tenancies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('tenancies');
        Schema::dropIfExists('tenancy_users');
        Schema::dropIfExists('rentables');
    }
}
