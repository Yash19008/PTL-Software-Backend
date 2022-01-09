<?php

namespace App\Models\V1\Operations;

use Illuminate\Database\Eloquent\Model;

class PushNotificationData extends Model
{
    protected $table = "push_not_data";
    
    public $timestamps = false;
    
    protected $fillable = [
        'id', 'push_not_id', 'client_id' , 'company_name', 'mobile', 'onesignal_id', 'size_in_inch', 'record', 'onesignal_ref_id', 'timestamp', 'message'
    ];
}
