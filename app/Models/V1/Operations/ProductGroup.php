<?php

namespace App\Models\V1\Operations;

use Illuminate\Database\Eloquent\Model;

class ProductGroup extends Model
{

    protected $table = "product_group";
    
    public $timestamps = false;

    protected $fillable = [
        'id', 'group_name', 'updated_on'
    ];
}
