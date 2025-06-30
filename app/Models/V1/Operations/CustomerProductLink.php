<?php

namespace App\Models\V1\Operations;

use Illuminate\Database\Eloquent\Model;

class CustomerProductLink extends Model
{

    protected $table = "customer_product_link";

    public $timestamps = false;

    protected $fillable = [
        'id', 'customer_id', 'product_group_id'
    ];
}
