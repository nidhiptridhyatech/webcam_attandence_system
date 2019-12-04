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
        'parent_id','first_name','last_name','phone_number','avatar','adhar_no','geofence_id','voice_enrollment_status','remaining_voice_enrollments',
    ];
}
