<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeofencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('geofences')) {
            Schema::create('geofences', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('user_id')->nullable(true);
                $table->string('address')->nullable(true);
                $table->double('latitude')->nullable(true);
                $table->double('longitude')->nullable(true);
                $table->integer('radius')->nullable(true);
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
        Schema::dropIfExists('geofences');
    }
}
