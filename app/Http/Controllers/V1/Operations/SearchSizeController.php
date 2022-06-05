<?php

namespace App\Http\Controllers\V1\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

use App\Models\V1\Operations\OptionMaster;
use App\Models\V1\Operations\ProductGroup;
use App\Models\V1\Operations\StockColumns;
use App\Models\V1\Operations\CustomerMaster;
use App\Models\V1\Operations\SearchHistoryMaster;

class SearchSizeController extends Controller {

    public function searchSize(Request $request) {
        $userlength = $request->get('length');
        $userwidth = $request->get('width');
        $gsm = $request->get('gsm');
        $group_name = $request->get('group_name');
        $size_in_inch = $userlength.' X '.$userwidth;

        $option_result = OptionMaster::where('option','gsm_range')->first();
        $gsm_range = $option_result->value;
        $upper_range = $gsm + $gsm_range;
        $lower_range = $gsm - $gsm_range;
            
        $where = " WHERE 1=1 ";
        if ($group_name) {
            $where = $where." AND product_group = '".$group_name."'";
        }
            
        $result = DB::select("
                SELECT 'PTL' as company, stock.product_group, stock.quality, gsm, dup.utiliz, dup.id, 
                CONCAT(size_inch_length,' X ',size_inch_width) as Size_INCH,size_inch_length ,size_inch_width,
                TRUNCATE(size_inch_length/".$userlength." ,0) AS LEN_UPS ,
                TRUNCATE(size_inch_width/".$userwidth." ,0) AS WID_UPS ,
                TRUNCATE(TRUNCATE(size_inch_length/".$userlength.",0)*TRUNCATE(size_inch_width/".$userwidth.",0),0) AS total_ups,
                TRUNCATE((pkt_grs_weight/sheet*100),1)  as  sheet_weight,
                (sheet*pkg_mode) as bundle,
                
                SUM(sheet*pkt_grs) as total_sheet
                
                FROM stock
                
                INNER JOIN
                    (SELECT id, (ROUND(((".$userlength."*".$userwidth.")/(size_inch_length*size_inch_width))*(TRUNCATE(TRUNCATE(size_inch_length/".$userlength.",0)*TRUNCATE(size_inch_width/".$userwidth.",0),0)) * 100)) as utiliz
                    FROM stock
                    WHERE stock.gsm BETWEEN ".$lower_range." AND ".$upper_range."
                    HAVING  utiliz >= (SELECT op.value FROM options_master op WHERE op.option='utilization_ups_admin') AND utiliz <= 100 
                    ORDER BY utiliz DESC, size_inch_length ASC) dup
                ON stock.id = dup.id
                
                ".$where."
                
                group by quality, gsm, Size_INCH
                ORDER BY utiliz DESC,gsm");
                
            $peptek_result = DB::select("
                SELECT 'Pap Tech' as company, peptek_stock.product_group, peptek_stock.quality, gsm, dup.utiliz, dup.id, 
                CONCAT(size_inch_length,' X ',size_inch_width) as Size_INCH,size_inch_length ,size_inch_width,
                TRUNCATE(size_inch_length/".$userlength." ,0) AS LEN_UPS ,
                TRUNCATE(size_inch_width/".$userwidth." ,0) AS WID_UPS ,
                TRUNCATE(TRUNCATE(size_inch_length/".$userlength.",0)*TRUNCATE(size_inch_width/".$userwidth.",0),0) AS total_ups,
                TRUNCATE((pkt_grs_weight/sheet*100),1)  as  sheet_weight,
                (sheet*pkg_mode) as bundle,
                
                SUM(sheet*pkt_grs) as total_sheet
                
                FROM peptek_stock
                
                INNER JOIN
                (SELECT id, (ROUND(((".$userlength."*".$userwidth.")/(size_inch_length*size_inch_width))*(TRUNCATE(TRUNCATE(size_inch_length/".$userlength.",0)*TRUNCATE(size_inch_width/".$userwidth.",0),0)) * 100)) as utiliz
                FROM peptek_stock
                WHERE peptek_stock.gsm BETWEEN ".$lower_range." AND ".$upper_range."
                HAVING  utiliz >= (SELECT op.value FROM options_master op WHERE op.option='utilization_ups_admin') AND utiliz <= 100 ORDER BY utiliz DESC, size_inch_length ASC) dup
                ON peptek_stock.id = dup.id
                
                ".$where."
                
                group by quality, gsm, Size_INCH
                ORDER BY utiliz DESC,gsm");
                
            $peptek_result = DB::select("
                    SELECT 'PTL Vendor' as company, stock_vendors.product_group, stock_vendors.quality, gsm, dup.utiliz, dup.id, 
                    CONCAT(size_inch_length,' X ',size_inch_width) as Size_INCH,size_inch_length ,size_inch_width,
                    TRUNCATE(size_inch_length/".$userlength." ,0) AS LEN_UPS ,
                    TRUNCATE(size_inch_width/".$userwidth." ,0) AS WID_UPS ,
                    TRUNCATE(TRUNCATE(size_inch_length/".$userlength.",0)*TRUNCATE(size_inch_width/".$userwidth.",0),0) AS total_ups,
                    TRUNCATE((pkt_grs_weight/sheet*100),1)  as  sheet_weight,
                    (sheet*pkg_mode) as bundle,
                    
                    SUM(sheet*pkt_grs) as total_sheet
                    
                    FROM stock_vendors
                    
                    INNER JOIN
                    (SELECT id, (ROUND(((".$userlength."*".$userwidth.")/(size_inch_length*size_inch_width))*(TRUNCATE(TRUNCATE(size_inch_length/".$userlength.",0)*TRUNCATE(size_inch_width/".$userwidth.",0),0)) * 100)) as utiliz
                    FROM stock_vendors
                    WHERE stock_vendors.gsm BETWEEN ".$lower_range." AND ".$upper_range."
                    HAVING  utiliz >= (SELECT op.value FROM options_master op WHERE op.option='utilization_ups_admin') AND utiliz <= 100 ORDER BY utiliz DESC, size_inch_length ASC) dup
                    ON stock_vendors.id = dup.id
                    
                    ".$where."
                    
                    group by quality, gsm, Size_INCH
                    ORDER BY utiliz DESC,gsm");
        $output = array_merge($result, $peptek_result);
        return $this->success('Search Size Responses List', $output, 200);
    }
    
    public function getMasters() {
        $data_record = ProductGroup::all();
        
        if($data_record) 
        { 
            $output['data']['product_group'] = $data_record;
            $output['message'] = 'Master Records !!';
            $output['status'] = 'success';
        }
        else
        {
           $output['message'] = 'Records not found !!';
            $output['status'] = 'error';
        }
        
        return response()->json($output, 200);
    
    }
    
    public function search_dynamic_column_wise(Request $request) {
        $customer_id = $request->get('customer_id');
        $output1['message'] = 'Size in Inch Record List !!';
        $output1['status'] = 'success';
        
        $result = StockColumns::where('customer_id', $customer_id)->first();
        
        if($result->quality == 'Yes')   $output1['headers']['quality'] = 'Quality';
        if($result->gsm == 'Yes')   $output1['headers']['gsm'] = 'GSM';
        if($result->size_inch == 'Yes')   $output1['headers']['size_inch'] = 'Size in Inch';
        if($result->total_ups == 'Yes')   $output1['headers']['total_ups'] = 'Total No. of UPS';
        if($result->utilization == 'Yes')   $output1['headers']['utilization'] = 'Utilization';
        if($result->sheet_weight == 'Yes')   $output1['headers']['sheet_weight'] = '100 Sheet Weight';
        if($result->bundle == 'Yes')   $output1['headers']['bundle'] = 'Per Bundle No. of Sheet';
        if($result->total_sheet == 'Yes')   $output1['headers']['total_sheet'] = 'Total Sheet';
        
        $data = $this->search_new_common($request, 'dynamic');
        $output1['data'] = $data;
        
        return response()->json($output1, 200);
    }
    
    
    public function search_new_common(Request $request, $return = 'api')
    {
        $userlength = $request->get('length');
        $userwidth = $request->get('width');
        $gsm = $request->get('gsm');
        $customer_id = $request->get('customer_id');
        $product_group = $request->get('product_group');

        $size_in_inch = $userlength.' X '.$userwidth;
        
        //  print_r($this->uri->segment(5));exit;
        
        $result_cust = CustomerMaster::where('id', $customer_id)->first();
        
        // insert query for maintaining history for how search made by particular person
        
        $data=array(
            "customer_id" => $result_cust->id,
            "company_name" => $result_cust->company_name,
            "client_name" => $result_cust->client_name,
            "email" => $result_cust->email,
            "mobile" => $result_cust->mobile,
            "width" => $userwidth,
            "heigth" => $userlength,
            "size_in_inch" => $size_in_inch,
            "gsm" => $gsm,
            "product_group" => $product_group,
            "timestamp" => date('Y-m-d H:i:s')
);

// insert into search history
SearchHistoryMaster::create($data);
        
$option_result = OptionMaster::where('option', 'gsm_range')->first();
        $gsm_range = $option_result->value;
        $upper_range = $gsm + $gsm_range;
        $lower_range = $gsm - $gsm_range;
        
        $where = " WHERE 1=1 ";
        if($product_group) {
            $where = $where." AND product_group = '".$product_group."'";
        }
             
        $result = DB::select("
                SELECT 'PTL' as company, stock.product_group, stock.quality, gsm, util.utilization, stock.id,
                CONCAT(size_inch_length,' X ',size_inch_width) as Size_INCH, size_inch_length ,size_inch_width,
                CONCAT(size_inch_length,' X ',size_inch_width) as size_inch,
                TRUNCATE(size_inch_length/".$userlength." ,0) AS LEN_UPS ,
                TRUNCATE(size_inch_width/".$userwidth." ,0) AS WID_UPS ,
                TRUNCATE(TRUNCATE(size_inch_length/".$userlength.",0)*TRUNCATE(size_inch_width/".$userwidth.",0),0) AS total_ups,
                TRUNCATE((pkt_grs_weight/sheet*100),1)  as  sheet_weight,
                (sheet*pkg_mode) as bundle,
                
                SUM(sheet*pkt_grs) as total_sheet
                
                FROM stock
                
                
                INNER JOIN
                    (SELECT id, (ROUND(((".$userlength."*".$userwidth.")/(size_inch_length*size_inch_width))*(TRUNCATE(TRUNCATE(size_inch_length/".$userlength.",0)*TRUNCATE(size_inch_width/".$userwidth.",0),0)) * 100)) as utilization
                    FROM stock
                    WHERE stock.gsm BETWEEN ".$lower_range." AND ".$upper_range." 
                    HAVING  utilization >= (SELECT op.value FROM options_master op WHERE op.option='utilization_ups') AND utilization <= 100 
                    ORDER BY utilization DESC, size_inch_length ASC) util
                ON stock.id = util.id
                
                ".$where."
                GROUP BY quality, gsm, Size_INCH
                
                
                UNION
                
                
                SELECT 'Pap Tech' as company, peptek_stock.product_group, peptek_stock.quality, gsm, util.utilization, peptek_stock.id,
                CONCAT(size_inch_length,' X ',size_inch_width) as Size_INCH,size_inch_length ,size_inch_width,
                CONCAT(size_inch_length,' X ',size_inch_width) as size_inch,
                TRUNCATE(size_inch_length/".$userlength." ,0) AS LEN_UPS ,
                TRUNCATE(size_inch_width/".$userwidth." ,0) AS WID_UPS ,
                TRUNCATE(TRUNCATE(size_inch_length/".$userlength.",0)*TRUNCATE(size_inch_width/".$userwidth.",0),0) AS total_ups,
                TRUNCATE((pkt_grs_weight/sheet*100),1)  as  sheet_weight,
                (sheet*pkg_mode) as bundle,
                
                SUM(sheet*pkt_grs) as total_sheet
                
                FROM peptek_stock 
                
                
                INNER JOIN
                    (SELECT id, (ROUND(((".$userlength."*".$userwidth.")/(size_inch_length*size_inch_width))*(TRUNCATE(TRUNCATE(size_inch_length/".$userlength.",0)*TRUNCATE(size_inch_width/".$userwidth.",0),0)) * 100)) as utilization
                    FROM peptek_stock
                    WHERE peptek_stock.gsm BETWEEN ".$lower_range." AND ".$upper_range." 
                    HAVING  utilization >= (SELECT op.value FROM options_master op WHERE op.option='utilization_ups') AND utilization <= 100 
                    ORDER BY utilization DESC, size_inch_length ASC) util
                ON peptek_stock.id = util.id
                
                ".$where."
                
                GROUP BY quality, gsm, Size_INCH
                
                
                UNION
                
                
                SELECT 'PTL Vendor' as company, stock_vendors.product_group, stock_vendors.quality, gsm, util.utilization, stock_vendors.id,
                CONCAT(size_inch_length,' X ',size_inch_width) as Size_INCH,size_inch_length ,size_inch_width,
                CONCAT(size_inch_length,' X ',size_inch_width) as size_inch,
                TRUNCATE(size_inch_length/".$userlength." ,0) AS LEN_UPS ,
                TRUNCATE(size_inch_width/".$userwidth." ,0) AS WID_UPS ,
                TRUNCATE(TRUNCATE(size_inch_length/".$userlength.",0)*TRUNCATE(size_inch_width/".$userwidth.",0),0) AS total_ups,
                TRUNCATE((pkt_grs_weight/sheet*100),1)  as  sheet_weight,
                (sheet*pkg_mode) as bundle,
                
                SUM(sheet*pkt_grs) as total_sheet
                
                FROM stock_vendors 
                
                
                INNER JOIN
                    (SELECT id, (ROUND(((".$userlength."*".$userwidth.")/(size_inch_length*size_inch_width))*(TRUNCATE(TRUNCATE(size_inch_length/".$userlength.",0)*TRUNCATE(size_inch_width/".$userwidth.",0),0)) * 100)) as utilization
                    FROM stock_vendors
                    WHERE stock_vendors.gsm BETWEEN ".$lower_range." AND ".$upper_range." 
                    HAVING  utilization >= (SELECT op.value FROM options_master op WHERE op.option='utilization_ups') AND utilization <= 100 
                    ORDER BY utilization DESC, size_inch_length ASC) util
                ON stock_vendors.id = util.id
                
                ".$where."
                
                GROUP BY quality, gsm, Size_INCH
                ORDER BY utilization DESC, gsm
                
                ");
        
        $data_record['list'] = $result;
        $data_record['stock_access'] = ($result_cust->stock_active == null) ? 0 : $result_cust->stock_active;
        $data_record = json_decode( json_encode($data_record), true);
        
        if($return == 'api')
        {
            $output1['data'] = $data_record;
            $output1['message'] = 'Size in Inch Record List !!';
            $output1['status'] = 'success';
            return response()->json($output1, 200);
        }
        else
        {
            return $data_record;
        }
    }

}
