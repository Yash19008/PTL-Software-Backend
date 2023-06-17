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
        $company = $request->get('company');
        $company = isset($company) ? $company : '';
        $size_in_inch = $userlength.' X '.$userwidth;

        $option_result = OptionMaster::where('option','gsm_range')->first();
        $gsm_range = $option_result->value;
        $upper_range = $gsm + $gsm_range;
        $lower_range = $gsm - $gsm_range;
            
        $where = " WHERE 1=1 AND weight > 0 ";
        if ($group_name) {
            $where = $where." AND product_group = '".$group_name."'";
        }
        $output = [];

        if ($company == '' || $company == 'PTL') {
            $output = $this->searchSizeQuery('mysql', 'PTL', $userlength, $userwidth, $lower_range, $upper_range, $where);
        }
        
        $admin_show_stocks_from = OptionMaster::where('option', 'admin_show_stocks_from')->first();
        $admin_show_stocks_from = explode(',', $admin_show_stocks_from->value);
        foreach($admin_show_stocks_from as $from) {
            if ($company == '' || $company == $from) {
                switch ($from) {
                    case 'Pap Tech':
                        $result = $this->searchSizeQuery('ptsc_connection', $from, $userlength, $userwidth, $lower_range, $upper_range, $where);
                        $output = array_merge($output, $result);
                    break;
                    case 'Paper Hub':
                        $result = $this->searchSizeQuery('paper_hub_connection', $from, $userlength, $userwidth, $lower_range, $upper_range, $where);
                        $output = array_merge($output, $result);
                    break;
                }
            }
        }
                
        $vendor_result = DB::select("
                SELECT usermaster.employee_name as company, stock_vendors.product_group, stock_vendors.quality, gsm, dup.utiliz, dup.id, 
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
                

                LEFT JOIN usermaster ON  usermaster.id = stock_vendors.vendor_id

                ".$where."
                
                group by quality, gsm, Size_INCH
                ORDER BY utiliz DESC,gsm");
        $output = array_merge($output, $vendor_result);
        usort($output, function($a, $b) { return $a->utiliz > $b->utiliz ? -1 : 1; });
        return $this->success('Search Size Responses List', $output, 200);
    }

    
    public function searchSizeQuery($connection, $name, $userlength, $userwidth, $lower_range, $upper_range, $where) {
        
        return \DB::connection($connection)->select("
            SELECT '".$name."' as company, stock.product_group, stock.quality, gsm, dup.utiliz, dup.id, 
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
                    ORDER BY utiliz DESC,gsm
                    ");
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
        if($result->gwd == 'Yes')   $output1['headers']['gwd'] = 'Godown';
        
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
        
        $where = " WHERE 1=1  AND weight > 0 ";
        if($product_group) {
            $where = $where." AND product_group = '".$product_group."'";
        }
             
        $output = $this->search_new_common_query('mysql', 'PTL', $userlength, $userwidth, $lower_range, $upper_range, $where);
        
        $mobile_show_stocks_from = OptionMaster::where('option', 'mobile_show_stocks_from')->first();
        $mobile_show_stocks_from = explode(',', $mobile_show_stocks_from->value);
        foreach($mobile_show_stocks_from as $from) {
            switch ($from) {
                case 'Pap Tech':
                    $result = $this->search_new_common_query('ptsc_connection', $from, $userlength, $userwidth, $lower_range, $upper_range, $where);
                    $output = array_merge($output, $result);
                break;
                case 'Paper Hub':
                    $result = $this->search_new_common_query('paper_hub_connection', $from, $userlength, $userwidth, $lower_range, $upper_range, $where);
                    $output = array_merge($output, $result);
                break;
            }
        }
        
        $stock_vendors_result = DB::select(" SELECT usermaster.employee_name as company, stock_vendors.product_group, stock_vendors.quality, gsm, util.utilization, stock_vendors.id, stock_vendors.gwd,
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

            LEFT JOIN usermaster ON  usermaster.id = stock_vendors.vendor_id
            
            ".$where."
            
            GROUP BY quality, gsm, Size_INCH
            ORDER BY utilization DESC, gsm
        
        ");
        $output = array_merge($output, $stock_vendors_result);
        usort($output, function($a, $b) { return $a->utilization > $b->utilization ? -1 : 1; });
        $data_record['list'] = $output;
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

    function search_new_common_query($connection, $name, $userlength, $userwidth, $lower_range, $upper_range, $where) {
        return \DB::connection($connection)->select("SELECT '".$name."' as company, stock.quality, gsm, util.utilization, stock.id, stock.gwd,
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
                (SELECT id, (ROUND(((".$userlength."*".$userwidth.")/(size_inch_length*size_inch_width))*(TRUNCATE(TRUNCATE(size_inch_length/".$userlength.",0)*TRUNCATE(size_inch_width/".$userwidth.",0),0)) * 100)) as utiliz
                FROM stock
                WHERE stock.gsm BETWEEN ".$lower_range." AND ".$upper_range." 
                HAVING  utiliz >= (SELECT op.value FROM options_master op WHERE op.option='utilization_ups') AND utiliz <= 100 
                ORDER BY utiliz DESC, size_inch_length ASC ) dup
            ON stock.id = dup.id
            
            INNER JOIN
                (SELECT id, (ROUND(((".$userlength."*".$userwidth.")/(size_inch_length*size_inch_width))*(TRUNCATE(TRUNCATE(size_inch_length/".$userlength.",0)*TRUNCATE(size_inch_width/".$userwidth.",0),0)) * 100)) as utilization
                FROM stock
                WHERE stock.gsm BETWEEN ".$lower_range." AND ".$upper_range." 
                HAVING  utilization >= (SELECT op.value FROM options_master op WHERE op.option='utilization_ups') AND utilization <= 100 
                ORDER BY utilization DESC, size_inch_length ASC) util
            ON stock.id = util.id
            
            ".$where."
            
            group by quality, gsm, Size_INCH
            
            ORDER BY utilization DESC,gsm");
    }

}
