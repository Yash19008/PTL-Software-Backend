<?php

namespace App\Models\V1\Operations;

use Illuminate\Database\Eloquent\Model;

class StockMaster extends Model
{
    protected $table = "stock";
    
    public $timestamps = false;
    
    protected $fillable = [
        'id', 'product_group', 'gsm' , 'size_inch_length', 'size_inch_width', 'size_cms_length', 'size_cms_width', 'pkt_grs_weight', 'sheet', 'bdls', 'pkt_grs', 'pkg_mode', 'weight', 'quality', 'gwd', 'loc', 'updated_on'
    ];
}
