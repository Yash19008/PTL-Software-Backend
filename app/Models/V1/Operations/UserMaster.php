<?php

namespace App\Models\V1\Operations;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class UserMaster extends Authenticatable implements JWTSubject
{
    protected $table = "usermaster";

    public $timestamps = false;

    protected $hidden = [
        'password'
    ];

    protected $fillable = [
        'id',
        'employee_name',
        'password',
        'email_id',
        'userlevel',
        'updated_dt',
        'updated_by',
        'user_status',
        'menus'
    ];

    /**
     * Required by JWTSubject
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Required by JWTSubject
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
