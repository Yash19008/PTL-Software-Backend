<?php

namespace App\Models\V1\Operations;

use Illuminate\Database\Eloquent\Model;

class CustomerQualityLink extends Model
{

    protected $table = "customer_quality_link";

    public $timestamps = false;

    protected $fillable = [
        'id', 'customer_id', 'quality_id'
    ];
}
