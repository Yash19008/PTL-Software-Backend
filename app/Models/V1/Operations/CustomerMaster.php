<?php

namespace App\Models\V1\Operations;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class CustomerMaster extends Authenticatable implements JWTSubject
{

    protected $table = "customer_master";
    
    public $timestamps = false;

    protected $fillable = [
        'id', 'Company_code', 'company_name', 'client_name', 'email', 'mobile', 'password', 'otp', 'Access_stk', 'active', 'stock_active', 'oneSignalUserId', 'oneSignalTokenId', 'updated_on', 'mobile_show_stocks_from',"device_info"
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
        return [];
    }
}
