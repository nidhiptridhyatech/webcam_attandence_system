<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users','role_id')) {
                $table->integer('role_id')->nullable(true);
            }
            if (!Schema::hasColumn('users','parent_id')) {
                $table->integer('parent_id')->nullable(true);
            }
            if (!Schema::hasColumn('users','avatar')) {
                $table->string('avatar')->nullable(true);
            }
            if (!Schema::hasColumn('users','first_name')) {
                $table->string('first_name')->nullable(true);
            }
            if (!Schema::hasColumn('users','last_name')) {
                $table->string('last_name')->nullable(true);
            }
            if (!Schema::hasColumn('users','phone_number')) {
                $table->string('phone_number')->nullable(true);
            }
        });
       
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
