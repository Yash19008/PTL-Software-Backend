<?php

namespace App\Models\V1\Operations;

use Illuminate\Database\Eloquent\Model;

class StockVendor extends Model
{
    protected $table = "stock_vendors";
    
    public $timestamps = false;
    
    protected $fillable = [
        'id', 'vendor_id', 'temp_flag', 'product_group', 'gsm' , 'size_inch_length', 'size_inch_width', 'size_cms_length', 'size_cms_width', 'pkt_grs_weight', 'sheet', 'bdls', 'pkt_grs', 'pkg_mode', 'weight', 'quality', 'gwd', 'loc', 'updated_on'
    ];
}
