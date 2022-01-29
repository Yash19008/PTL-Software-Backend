<?php

namespace App\Models\V1\Operations;

use Illuminate\Database\Eloquent\Model;

class OrderMaster extends Model
{

    protected $table = "order_master";
    
    public $timestamps = false;

    protected $fillable = [
        'id', 'date', 'cust_id', 'quality', 'size_inch_length', 'size_inch_width' , 'gsm', 'product_group', 'qty', 'weight', 'delivery_at', 'status', 'sync_status', 'challan_number', 'last_searched_id', 'updated_dt', 'update_by'
    ];
}
