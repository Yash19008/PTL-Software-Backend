<?php

namespace App\Http\Controllers\V1\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TempController extends Controller
{
    public function temp_stock_import(Request $request){
        if(!empty($request->all())){
            return $this->success('Import outside stock successfully !!', $request->all(), 200);
        }else{
            return $this->failure('Something Went Wrong !!', $e->getMessage(), 500);
        }
        
    }
}
