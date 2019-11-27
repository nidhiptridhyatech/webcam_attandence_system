<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToFrontUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('front_users', function (Blueprint $table) {
            $table->string('voice_profile_id')->nullable(true);
            $table->tinyInteger('voice_enrollment_status')->comment('1=>enrolled, 0=>Not enrolled')->default(0);
            $table->tinyInteger('remaining_voice_enrollments')->comment('number of remaining enrollment required out of 3 for succcessfull enrollment.')->default(3);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('front_users', function (Blueprint $table) {
            $table->dropColumn(['voice_profile_id', 'voice_enrollment_status', 'remaining_voice_enrollments']);
        });
    }
}
