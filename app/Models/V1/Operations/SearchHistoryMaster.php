<?php

namespace App\Models\V1\Operations;

use Illuminate\Database\Eloquent\Model;

class SearchHistoryMaster extends Model
{

    protected $table = "search_history_master";
    
    public $timestamps = false;

    protected $fillable = [
        'id', 'customer_id', 'company_name', 'client_name', 'email', 'mobile' , 'width', 'heigth', 'size_in_inch', 'gsm', 'qty', 'product_group', 'timestamp','platform'
    ];
}
