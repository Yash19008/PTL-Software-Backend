<?php

namespace App\Models\V1\Operations;

use Illuminate\Database\Eloquent\Model;

class CustomerMaster extends Model
{

    protected $table = "customer_master";
    
    public $timestamps = false;

    protected $fillable = [
        'id', 'Company_code', 'company_name', 'client_name', 'email', 'mobile' , 'password', 'otp', 'Access_stk', 'active', 'stock_active', 'oneSignalUserId', 'oneSignalTokenId', 'updated_on'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];
}
