<?php

namespace App\Models\V1\Operations;

use Illuminate\Database\Eloquent\Model;

class ChallanList extends Model
{
    protected $table = "challan_list";
    
    public $timestamps = false;
    
    protected $fillable = [
        'id', 'customer_name', 'mobile' , 'date', 'challan_no', 'quality', 'size_inch_length', 'size_inch_width', 'gsm', 'bdls', 'pkt_grs', 'sheets', 'weight', 'delivery_at', 'status', 'updated_on'
    ];
}
