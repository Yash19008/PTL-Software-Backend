<?php

namespace App\Http\Controllers\V1\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\V1\Operations\StockMaster;
use App\Models\V1\Operations\PeptekStock;

class StockMasterController extends Controller {

    public function query()
    {
        $query = StockMaster::select("*");
        return $query;
    }

    public function index()
    {
        $limit = \Config::get('global.PAGINATE.limit');
        $query = $this->query();
        $tablesColumns = $this->getTableColumn();
        $query = $this->search($query, $tablesColumns, \Request::get('search'));
        $query = $this->sort($query, $tablesColumns, \Request::get('sort'), 'id');
        $query = $query->paginate($limit);
        return $this->success('OrderMaster Responses List', $query, 200);
    }

    public function getTableColumn()
    {         
        return array( "id" => "id", "product_group" => "product_group" , "gsm" => "gsm", "size_inch_length" => "size_inch_length", "size_inch_width" => "size_inch_width", "size_cms_length" => "size_cms_length", "size_cms_width" => "size_cms_width", "pkt_grs_weight" => "pkt_grs_weight", "sheet" => "sheet", "bdls" => "bdls", "pkt_grs" => "pkt_grs", "pkg_mode" => "pkg_mode", "weight" => "weight", "quality" => "quality", "gwd" => "gwd", "loc" => "loc", "updated_on" => "updated_on");
    }

    public function GetStockdetail(Request $request) {
        $stock_id = $request->get('stock_id');
        
        // search from PTL Stock
        $data_record1 = StockMaster::where('id', $stock_id)->first();
        // print_r($this->db->last_query());exit;
        if($data_record1) 
        {
            $data = $data_record1;
        }
        
        // search from Paptech Stock
        $data_record2 = PeptekStock::where('id', $stock_id)->first();
        // print_r($this->db->last_query());exit;
        if($data_record2 && $data == NULL) 
        {
            $data = $data_record2;
        }
        
        
        if($data) 
        { 
            $output['data'] = $data;
            $output['message'] = 'Size in Inch Detail !!';
            $output['status'] = 'success';
        }
        else
        {
           $output['message'] = 'Size not found !!';
            $output['status'] = 'error';
        }
        
        echo json_encode($output, JSON_NUMERIC_CHECK);
    
    }

    public function full_stock()
    {
        $data_record = StockMaster::all();
        echo json_encode($data_record, JSON_NUMERIC_CHECK);
    }

}
