<?php

namespace App\Models\V1\Operations;

use Illuminate\Database\Eloquent\Model;

class OptionMaster extends Model
{

    protected $table = "options_master";
    
    public $timestamps = false;

    protected $fillable = [
        'id', 'option', 'detail', 'value', 'status', 'updated_dt' , 'updated_by', 'menus'
    ];
}
