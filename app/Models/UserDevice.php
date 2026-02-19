<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\V1\Operations\CustomerMaster;

class UserDevice extends Model
{
    protected $fillable = [
        'user_id',
        'device_type',
        'device_id',
        'device_info',
        'token',
        'active',
        'ip_address',
        'user_agent',
        'onesignal_user_id',
        'onesignal_token_id',
        'last_active_at'
    ];

    protected $casts = [
        'last_active_at' => 'datetime',
        'device_info' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(CustomerMaster::class, 'user_id');
    }
}
