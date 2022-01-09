<?php

namespace App\Models\V1\Operations;

use Illuminate\Database\Eloquent\Model;

class PushNotification extends Model
{
    protected $table = "push_notification";
    
    public $timestamps = false;
    
    protected $fillable = [
        'id', 'hours', 'type' , 'utilisation', 'message'
    ];
}
