<?php

namespace App\Models\V1\Operations;

use Illuminate\Database\Eloquent\Model;

class VendorHistory extends Model
{

    protected $table = "vendor_upload_history";
    
    public $timestamps = false;

    protected $fillable = [
        'id', 'user_id', 'created_at', 'updated_at'
    ];
}
