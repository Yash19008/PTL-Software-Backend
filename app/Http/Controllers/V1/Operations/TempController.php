<?php

namespace App\Http\Controllers\V1\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TempController extends Controller
{
    public function temp_stock_import(Request $request){
        echo '<pre>';print_r($request->all());echo '</pre>';exit();
        
    }
}
