<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HospitalType extends Model
{

    public function account()
    {
        return $this->hasMany(JLAccount::class, 'hospital_id', 'id');
    }


//    public function getRobotAttribute($value)
//    {
//        return explode(',', $value);
//    }
//
//    public function setRobotAttribute($value)
//    {
//        $this->attributes['robot'] = implode(',', $value);
//    }

}
