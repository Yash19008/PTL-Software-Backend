<?php

namespace App\Models\V1\Operations;

use Illuminate\Database\Eloquent\Model;

class UserMaster extends Model
{

    protected $table = "usermaster";
    
    public $timestamps = false;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    
    protected $hidden = [
        'password'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'employee_name', 'password', 'email_id', 'userlevel', 'updated_dt' , 'updated_by', 'user_status', 'menus'
    ];
}
