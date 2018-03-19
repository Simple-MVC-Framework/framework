<?php

use Nova\Database\Schema\Blueprint;
use Nova\Database\Migrations\Migration;


class CreateContactsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('contacts');

        Schema::create('contacts', function (Blueprint $table)
        {
            $table->increments('id');

            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->text('path')->nullable();
            $table->text('description')->nullable();

            $table->integer('count')->unsigned()->default(0);

            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();

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
        Schema::dropIfExists('contacts');
    }
}
