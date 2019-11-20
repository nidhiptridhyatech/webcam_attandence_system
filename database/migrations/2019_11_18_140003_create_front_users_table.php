<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFrontUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('front_users')) {
            Schema::create('front_users', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('parent_id')->nullable(true);
                $table->string('first_name')->nullable(true);
                $table->string('last_name')->nullable(true);
                $table->string('phone_number')->nullable(true);
                $table->string('avatar')->nullable(true);
                $table->integer('geofence_id')->nullable(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('front_users');
    }
}
