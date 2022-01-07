<?php

namespace App\Models\V1\Operations;

use Illuminate\Database\Eloquent\Model;

class OrderMasterView extends Model
{

    protected $table = "order_master_view";
    
    public $timestamps = false;

    protected $fillable = [
        'id', 'date', 'cust_id', 'company_name', 'quality', 'size_inch_length', 'size_inch_width' , 'gsm', 'qty', 'weight', 'delivery_at', 'status', 'challan_number'
    ];
}
