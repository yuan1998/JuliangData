<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JLApp extends Model
{
    protected $fillable = [
        'app_id',
        'app_secret',
        'name',
        'use_count',
        'limit_count',
    ];
}
