<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class FrontUser extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'voice_enrollment_status','remaining_voice_enrollments',
    ];
}
