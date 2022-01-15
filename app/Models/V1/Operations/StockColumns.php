<?php

namespace App\Models\V1\Operations;

use Illuminate\Database\Eloquent\Model;

class StockColumns extends Model
{

    protected $table = "stock_columns";
    
    public $timestamps = false;

    protected $fillable = [
        'id', 'customer_id', 'quality', 'gsm', 'size_inch', 'total_ups' , 'utilization', 'sheet_weight', 'bundle', 'total_sheet'
    ];
}
