<?php

namespace App\Models\V1\Operations;

use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    protected $table = "audittrail";
    
    public $timestamps = false;
    
    protected $fillable = [
        'id', 'module', 'user' , 'action', 'ipaddress', 'oldvalue', 'newvalue'
    ];
}
