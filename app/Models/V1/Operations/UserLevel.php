<?php

namespace App\Models\V1\Operations;

use Illuminate\Database\Eloquent\Model;

class UserLevel extends Model
{
    protected $table = "_userlevels";
    
    public $timestamps = false;
    
    protected $fillable = [
        'id', 'userlevelsname', 'operation_permission' , 'active'
    ];
}
