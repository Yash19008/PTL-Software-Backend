<?php

namespace App\Http\Controllers\V1\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\V1\Operations\ChallanList;
use DB;

class ChallanController extends Controller {

    public function query()
    {
        $query = ChallanList::select("*");
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
        return array( "id" => "id", "customer_name" => "customer_name" , "mobile" => "mobile", "date" => "date", "challan_no" => "challan_no", "quality" => "quality", "size_inch_length" => "size_inch_length", "size_inch_width" => "size_inch_width", "gsm" => "gsm", "bdls" => "bdls", "pkt_grs" => "pkt_grs", "sheets" => "sheets", "weight" => "weight", "delivery_at" => "delivery_at", "status" => "status", "updated_on" => "updated_on");
    }

    public function get_challan_list(Request $request)
    {   
        $whr = " ";
        $mobile_no = $request->get('mobile');
        if($mobile_no)
        {
            $whr = " mobile='".$mobile_no."'";
        }
        
        $challan_id = $request->get('challan_id');
        if($challan_id)
        {
           $whr= "  id = ".$challan_id;
        }
        
        $challan_no = $request->get('challan_no');
        if($challan_no)
        {
           $whr= "  challan_no = '".$challan_no."'";
        }
        
        //fetch challan records from challan_list table with respect mobile no.
        $data_record = DB::select("SELECT *,date_format(date(date),'%d-%m-%Y') as Date_challan 
        FROM challan_list WHERE 1=1 AND ". $whr ." 
        GROUP BY challan_no 
        ORDER BY date desc, challan_no desc");
        if(count($data_record) > 0)
        {
            $output['data'] = $data_record;
            $output['message'] = 'Challan Details !!';
            $output['status'] = 'success';
        }
        else
        {
            $output['message'] = 'No record found !!';
            $output['status'] = 'error';
        }
        return response()->json($output, 200);
    }
    
    public function detials(Request $request) {   
        $challan_no = $request->get('challan_no');
        if($challan_no)
        {
            $data_record = DB::select("SELECT *,date_format(date(date),'%d-%m-%Y') as Date_challan, SUM(sheets) as sheets 
            FROM `challan_list` WHERE `challan_no` = '$challan_no' GROUP BY customer_name, challan_no, quality, size_inch_length, size_inch_width, gsm");
            
            // remove stdclass from $data_record
            $data_record = json_decode( json_encode($data_record), true);
            if(count($data_record) > 0)
            {
                $output['data'] = $data_record;
                $output['message'] = 'Challan Details !!';
                $output['status'] = 'success';
            }
            else
            {
                $output['message'] = 'No record found !!';
                $output['status'] = 'error';
            }
        }
        else
        {
            $output['message'] = 'No record found !!';
            $output['status'] = 'error';
        }
        return response()->json($output, 200);
    }

}
