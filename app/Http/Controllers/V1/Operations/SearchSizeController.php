<?php

namespace App\Http\Controllers\V1\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

use App\Models\V1\Operations\OptionMaster;
use App\Models\V1\Operations\ProductGroup;

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
        
        echo json_encode($output, JSON_NUMERIC_CHECK);
    
    }

}
