<?php

namespace App\Models\V1\Operations;

use Illuminate\Database\Eloquent\Model;

class QualityMaster extends Model
{

    protected $table = "quality_master";

    public $timestamps = false;

    protected $fillable = [
        'id', 'name','updated_at'
    ];
}
