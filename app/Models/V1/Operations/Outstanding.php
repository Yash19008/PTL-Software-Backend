<?php

namespace App\Models\V1\Operations;

use Illuminate\Database\Eloquent\Model;

class Outstanding extends Model
{
    protected $table = "outstanding";
    
    public $timestamps = false;
    
    protected $fillable = [
        'id', 'date', 'customer_name' , 'person_name', 'mobile', 'email', 'voucher_type', 'voucher_no', 'credit_days', 'due_date', 'over_dues', 'total_amount', 'part_paid', 'balance'
    ];
}
