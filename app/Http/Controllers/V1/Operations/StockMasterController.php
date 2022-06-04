<?php

namespace App\Http\Controllers\V1\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

    public function getTableColumn()
    {         
        return array( "id" => "id", "product_group" => "product_group" , "gsm" => "gsm", "size_inch_length" => "size_inch_length", "size_inch_width" => "size_inch_width", "size_cms_length" => "size_cms_length", "size_cms_width" => "size_cms_width", "pkt_grs_weight" => "pkt_grs_weight", "sheet" => "sheet", "bdls" => "bdls", "pkt_grs" => "pkt_grs", "pkg_mode" => "pkg_mode", "weight" => "weight", "quality" => "quality", "gwd" => "gwd", "loc" => "loc", "updated_on" => "updated_on");
    }

    public function GetStockdetail(Request $request) {
        $stock_id = $request->get('stock_id');
        $data = NULL;
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
        
        return response()->json($output, 200);
    
    }

    public function import(Request $request)
    {
        ini_set('max_execution_time', 600000);
        ini_set('memory_limit', '2048M');
        $errorsObj = new \stdClass();
        try {
            $vendorId = \Auth::user()->id;
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

}
