<?php

namespace App\Models\V1\Operations;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class CustomerMaster extends Authenticatable implements JWTSubject
{

    protected $table = "customer_master";
    
    public $timestamps = false;

    protected $fillable = [
        'id', 'Company_code', 'company_name', 'client_name', 'email', 'mobile', 'password', 'otp', 'Access_stk', 'active', 'stock_active', 'oneSignalUserId', 'oneSignalTokenId', 'updated_on', 'mobile_show_stocks_from', 'device_info', 'max_allowed_devices', 'maximum_allowed_device'
    ];
    
    protected $casts = [
        'device_info' => 'array'
    ];

    protected $hidden = [
        'password'
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'guard' => 'customer'
        ];
    }

    public function devices()
    {
        return $this->hasMany(\App\Models\UserDevice::class, 'user_id')->where('active', 1);
    }

    public function getMaxAllowedDevices(): int
    {
        $value = $this->max_allowed_devices ?? $this->maximum_allowed_device ?? 0;
        return (int) $value;
    }

    public function hasUnlimitedDevices(): bool
    {
        $max = $this->max_allowed_devices ?? $this->maximum_allowed_device;
        return $max === null;
    }
}
