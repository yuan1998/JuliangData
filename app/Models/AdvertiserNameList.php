<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvertiserNameList extends Model
{
    protected $fillable = [
        'advertiser_id',
        'advertiser_name',
        'comment',
        'account_id',
        'hospital_id',
    ];

    public function account()
    {
        return $this->hasOne(JLAccount::class, 'id', 'account_id');
    }

    public function hospital()
    {
        return $this->hasOne(HospitalType::class, 'id', 'hospital_id');
    }


    public static function makeList($list)
    {
        foreach ($list as $item) {
            static::updateOrCreate([
                'advertiser_id'   => $item['advertiser_id'],
                'account_id'      => $item['id'],
                'hospital_id'     => $item['hospital_id'],
                'advertiser_name' => $item['advertiser_name'],
            ]);
        }

    }
}
