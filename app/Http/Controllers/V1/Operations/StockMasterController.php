<?php

namespace App\Http\Controllers\V1\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\V1\Operations\StockMaster;
use App\Models\V1\Operations\PeptekStock;
use App\Models\V1\Operations\Imports\VendorStocksImport;
use App\Models\V1\Operations\StockVendor;
use App\Models\V1\Operations\VendorHistory;

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
    
    public function totalWeight() {
        $sum = StockMaster::sum('weight');
        return $this->success('Total sum of weights', $sum, 200);
    }

    public function getTableColumn()
    {         
        return array( "id" => "id", "product_group" => "product_group" , "gsm" => "gsm", "size_inch_length" => "size_inch_length", "size_inch_width" => "size_inch_width", "size_cms_length" => "size_cms_length", "size_cms_width" => "size_cms_width", "pkt_grs_weight" => "pkt_grs_weight", "sheet" => "sheet", "bdls" => "bdls", "pkt_grs" => "pkt_grs", "pkg_mode" => "pkg_mode", "weight" => "weight", "quality" => "quality", "gwd" => "gwd", "loc" => "loc", "updated_on" => "updated_on");
    }

    public function GetStockdetail(Request $request) {
        $stock_id = $request->get('stock_id');
        $company = $request->get('company');
        $data = NULL;

        if ($company == 'Pap Tech') {
            // search from PTL Stock
            $data_record1 = StockMaster::where('id', $stock_id)->first();
            // print_r($this->db->last_query());exit;
            if ($data_record1) {
                $data = $data_record1;
            }
        } else if ($company == 'PTL') {
            // search from Paptech Stock
            $data_record2 = $this->get_data_from_connection('ptl_connection', 'getStock', $stock_id);
            if (isset($data_record2)) {
                $data = $data_record2;
            }
        } else if ($company == 'Paper Hub' || $company == 'Pap Tech - Ahmedabad') {
            $data_record2 = $this->get_data_from_connection('paper_hub_connection', 'getStock', $stock_id);
            if (isset($data_record2)) {
                $data = $data_record2;
            }
        } else if ($company == 'Parekh') {
            $data_record2 = $this->get_data_from_connection('parekh_connection', 'getStock', $stock_id);
            if (isset($data_record2)) {
                $data = $data_record2;
            }
        } else {
            // search from Paptech Stock
            $data_record3 = StockVendor::where('id', $stock_id)->first();
            // print_r($this->db->last_query());exit;
            if ($data_record3) {
                $data = $data_record3;
            }
        }


        if ($data) {
            $output['data'] = $data;
            $output['message'] = 'Size in Inch Detail !!';
            $output['status'] = 'success';
        }
        else
        {
           $output['message'] = 'Size not found !!';
            $output['status'] = 'error';
        }
        
        return response()->json($output, 200);
    
    }

    public function full_stock()
    {
        $where = '';
        if(isset($_REQUEST['quality']))
        {
            $quality = $_REQUEST['quality'];
            $where = explode(";", $quality);
            array_shift($where);
            $query =  StockMaster::select("*");
            foreach($where as $value)
            {
                $query->orWhere('quality', $value);
            }
            $data_record = $query->get();
        } else {
            $data_record = StockMaster::all();
        }
        return response()->json($data_record, 200);
    }

    public function unique_quality()
    {
        $data_record = StockMaster::distinct('quality')->pluck('quality');
        return response()->json($data_record, 200);
    }

    public function import(Request $request)
    {
        ini_set('max_execution_time', 600000);
        ini_set('memory_limit', '2048M');
        $errorsObj = new \stdClass();
        try {
            $vendorId = $request->get('vendor_id');
            if (empty($vendorId) || $vendorId == null || $vendorId == 'null') {
                $vendorId = \Auth::user()->id;
            }
            if (is_file($request->file)) {
                $import = new VendorStocksImport($vendorId, true);
                $rowsCount = 0;
                try {
                    \Excel::import($import, $request->file);
                    $rowsCount = $import->getRowCount();
                } catch (\Exception $e) {
                    $rowsCount = $import->getRowCount();
                }

                if ($rowsCount > 2000) {
                    return $this->failure('Please provide only 2000 records at a time in excel to import stocks','',500);
                } else if($rowsCount == 0) {
                    return $this->failure('Excel data is not in correct format.','',500);
                } else {
                    $import = new VendorStocksImport($vendorId, false);
                    try {
                        \Excel::import($import, $request->file);

                        StockVendor::where("vendor_id", $vendorId)->where("temp_flag", 0)->delete();
                        StockVendor::where("vendor_id", $vendorId)->where("temp_flag", 1)->update([ "temp_flag" => 0]);

                        $history = [
                            'user_id'     => $vendorId
                        ];
                        VendorHistory::create($history);

                    } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                        $failures = $e->failures();
                        $errorsArray = [];
                        foreach ($failures as $failure) {
                            if (!$this->containsOnlyNull($failure->values())) {
                                $errorsObj->rowIndex = $failure->row();
                                $errorsObj->errorsArray[] = $failure->errors()[0];
                                \Log::info($failure->row()); // row that went wrong
                                // \Log::info($failure->attribute()); // either heading key (if using heading row concern) or column index
                                // \Log::info($failure->errors()); // Actual error messages from Laravel validator
                                // \Log::info($failure->values()); // The values of the row that has failed.
                            }
                        }
                    }
                }
                return $this->success('Import successfull !!', $errorsObj, 200);
            }
            return $this->failure('Select file !!', $e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->failure('Something Went Wrong !!', $e->getMessage(), 500);
        }
    }
    
    function containsOnlyNull($input)
    {
        return empty(array_filter($input, function ($a) { return $a !== null;}));
    }

    
    public function ClearVendorStock(Request $request) {
        try {
            StockVendor::where("vendor_id", $request->vendor_id)->delete();
            return $this->success('Vendor Stocks Deleted Successfully !!', null, 200);
        } catch (\Exception $e) {
            return $this->failure('Vendor Stocks Deleted Successfully !!', $e->getMessage(), 500);
        }
    }
    public function import_stock_outside(Request $request){
        /*
            stockObj = [
                {
                    "product_group" : "GREY BACK",
                    "gsm": 310,
                    "size_inch_length":19.3,
                    "size_inch_width":39.8,
                    "size_cms_length":49,
                    "size_cms_width":101,
                    "pkt_grs_weight": 22.1,
                    "sheet":144,
                    "bdls":7,
                    "pkg_mode":3,
                    "pkt_grs":21,
                    "weight":464.1,
                    "quality":"3M DUPLEX BOARD HWC",
                    "godown":"GN 2",
                    "location":"1/K-6"
                },
                {
                    "product_group" : "GREY BACK",
                    "gsm": 296,
                    "size_inch_length":31.5,
                    "size_inch_width":41.54,
                    "size_cms_length":80,
                    "size_cms_width":105.5,
                    "pkt_grs_weight": 25,
                    "sheet":100,
                    "bdls":1,
                    "pkg_mode":2,
                    "pkt_grs":2,
                    "weight":50,
                    "quality":"3M PEARL WHITE GB",
                    "godown":"GN 2",
                    "location":"A-35"
                },
                {
                 "product_group" : "GREY BACK",
                    "gsm": 296,
                    "size_inch_length":32,
                    "size_inch_width":41.5,
                    "size_cms_length":81.5,
                    "size_cms_width":105.5,
                    "pkt_grs_weight": 18.3,
                    "sheet":72,
                    "bdls":1,
                    "pkg_mode":3,
                    "pkt_grs":2,
                    "weight":36.6,
                    "quality":"3M PEARL WHITE GB",
                    "godown":"GN 2",
                    "location":"D-25"
                }
            ]
         */
        try {
           // $stockArr = json_decode($request->stockObj, true);
            //echo '<pre>';print_r($stockArr);echo '</pre>';exit();
            if(!empty($request->all())){
                foreach ($request->all() as $key => $value) {
                    $stock = StockMaster::where('gsm',$value['gsm'])->where('size_inch_length',$value['size_inch_length'])->where('size_inch_width',$value['size_inch_width'])->where('quality',$value['quality'])->where('pkg_mode',$value['pkg_mode'])->where('gwd',$value['godown'])->where('eta',$value['eta'])->where('loc',$value['location'])->get();
                   
                    if(count($stock) == 0){
                        $insert_stock_array=array(
                            'product_group'=>$value['product_group'],
                            'gsm'=>$value['gsm'],
                            'size_inch_length'=>$value['size_inch_length'],
                            'size_inch_width'=>$value['size_inch_width'],
                            'size_cms_length'=>$value['size_cms_length'],
                            'size_cms_width' => $value['size_cms_width'],
                            'pkt_grs_weight'=>$value['pkt_grs_weight'],
                            'sheet'=>$value['sheet'],
                            'bdls'=>$value['bdls'],
                            'pkg_mode'=>$value['pkg_mode'],
                            'pkt_grs'=>$value['pkt_grs'],
                            'weight'=>$value['weight'],
                            'quality'=>$value['quality'],
                            'gwd'=>$value['godown'],
                            'loc'=>$value['location'],
                            'eta'=>$value['eta'],
                            'updated_on'=>Carbon::now(),
                        );
                        StockMaster::create($insert_stock_array);
                    }else{
                        foreach ($stock as $key => $val) {
                            $update_stock_array=array(
                                'product_group'=>$value['product_group'],
                                'gsm'=>$value['gsm'],
                                'size_inch_length'=>$value['size_inch_length'],
                                'size_inch_width'=>$value['size_inch_width'],
                                'size_cms_length'=>$value['size_cms_length'],
                                'size_cms_width' => $value['size_cms_width'],
                                'pkt_grs_weight'=>$value['pkt_grs_weight'] ,
                                'sheet'=>$value['sheet'] ,
                                'bdls'=>$value['bdls'],
                                'pkg_mode'=>$value['pkg_mode'],
                                'pkt_grs'=>$value['pkt_grs'] ,
                                'weight'=>$value['weight'],
                                'quality'=>$value['quality'],
                                'gwd'=>$value['godown'],
                                'loc'=>$value['location_new'] != '' ? $value['location_new'] : $value['location'],
                                'eta'=>$value['eta'],
                                'updated_on'=>Carbon::now(),
                            );
                            
                            StockMaster::where('gsm',$value['gsm'])->where('size_inch_length',$value['size_inch_length'])->where('size_inch_width',$value['size_inch_width'])->where('quality',$value['quality'])->where('pkg_mode',$value['pkg_mode'])->where('gwd',$value['godown'])->where('loc',$value['location'])->update($update_stock_array);
                        }
                    }
                }
                return $this->success('Import outside stock successfully !!', $request->all(), 200);
            }else{
                return $this->failure('Empty data found or Data not proper !!',  500);
            }
        } catch (\Exception $e) {
            return $this->failure('Something Went Wrong !!', $e->getMessage(), 500);
        }
    }
    public function deleteAllStock(){
        StockMaster::truncate();
        return $this->success('Stock table truncated successfully !!', 200);
    }
}
