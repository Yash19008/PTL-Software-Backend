<?php

namespace App\Http\Controllers\V1\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
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
    public function import_challan_outside(Request $request){
        /*
            challanObj = [
                {
                    "date" : "2023-03-29",
                    "challan_no": "G-9965",
                    "quality":"TITAN HI-BRITE GB HWC",
                    "size_inch_length":28,
                    "size_inch_width":52,
                    "gsm":230,
                    "bdls": 1,
                    "pkt_grs":1,
                    "sheets":144,
                    "weight":31,
                    "delivery_at":"KANKESHWAR LAMINATORS",
                    "status":"PENDING"
                },
                {
                    "date" : "2023-03-29",
                    "challan_no": "G-9965",
                    "quality":"TITAN HI-BRITE GB HWC",
                    "size_inch_length":28,
                    "size_inch_width":52,
                    "gsm":230,
                    "bdls": 1,
                    "pkt_grs":1,
                    "sheets":144,
                    "weight":31,
                    "delivery_at":"KANKESHWAR LAMINATORS",
                    "status":"PENDING"
                }

            ]
        */
        //ADD STATUS AS "PENDING"/"DELIVERED"
         try {
            //$challanArr = json_decode($request->challanObj, true);
            if(!empty($request->all())){
                foreach ($request->all() as $key => $value) {
                    $challan = ChallanList::where('challan_no',$value['challan_no'])->get();
                    if(count($challan) == 0){
                        $insert_challan_array=array(
                            "customer_name"=> $value['customer_name'],
                            "mobile"=>$value['mobile'],
                            "date" => date('Y-m-d', strtotime($value['date'])),
                            "challan_no"=> $value['challan_no'],
                            "quality"=>$value['quality'],
                            "size_inch_length"=>$value['size_inch_length'],
                            "size_inch_width"=>$value['size_inch_width'],
                            "gsm"=>$value['gsm'],
                            "bdls"=> $value['bdls'],
                            "pkt_grs"=>$value['pkt_grs'],
                            "sheets"=>$value['sheets'],
                            "weight"=>$value['weight'],
                            "delivery_at"=>$value['delivery_at'],
                            "status"=>$value['status'],
                            "updated_on"=>Carbon::now(),
                        );    
                        ChallanList::create($insert_challan_array);

                    }else{
                        foreach ($challan  as $key => $val) {
                            $update_challan_array=array(
                                "customer_name"=> $value['customer_name'],
                                "mobile"=>$value['mobile'],
                                "date" => date('Y-m-d', strtotime($value['date'])),
                                "quality"=>$value['quality'],
                                "size_inch_length"=>$value['size_inch_length'],
                                "size_inch_width"=>$value['size_inch_width'],
                                "gsm"=>$value['gsm'],
                                "bdls"=> $value['bdls'],
                                "pkt_grs"=>$value['pkt_grs'],
                                "sheets"=>$value['sheets'],
                                "weight"=>$value['weight'],
                                "delivery_at"=>$value['delivery_at'],
                                "status"=>$value['status'],
                                "updated_on"=>Carbon::now(),
                            );
                            ChallanList::where('challan_no',$value['challan_no'])->update($update_challan_array);
                        }
                      
                    }
                }
                return $this->success('Import outside challan successfully !!', $request->all(), 200);
              
            }else{
                return $this->failure('Empty data found or Data not proper !!', 500);
            }
        } catch (\Exception $e) {
            return $this->failure('Something Went Wrong !!', $e->getMessage(), 500);
        }
    }
    public function deleteAllChallan(){
        ChallanList::truncate();
        return $this->success('Challan table truncated successfully !!', 200);
    }
       public function import_challan_outside_nested(Request $request){
       
        //ADD STATUS AS "PENDING"/"DELIVERED"
         try {
            //$challanArr = json_decode($request->challanObj, true);
            if(!empty($request->all())){
                foreach ($request->all() as $key => $value) {
                    $challan = ChallanList::where('challan_no',$value['challan_no'])->get();
                    if(count($challan) == 0){
                        foreach ($value['items'] as $key => $val) {
                            $input = [
                                "customer_name"=> $value['customer_name'],
                                "mobile"=>$value['mobile'],
                                "date" => date('Y-m-d', strtotime($value['date'])),
                                "challan_no"=> $value['challan_no'],
                                "delivery_at"=>$value['delivery_at'],
                                "status"=>$value['status'],
                                "quality"=>$val['quality'],
                                "size_inch_length"=>$val['size_inch_length'],
                                "size_inch_width"=>$val['size_inch_width'],
                                "gsm"=>$val['gsm'],
                                "bdls"=> $val['bdls'],
                                "pkt_grs"=>$val['pkt_grs'],
                                "sheets"=>$val['sheets'],
                                "weight"=>$val['weight'],
                                'updated_on'=>Carbon::now()
                            ];
                            ChallanList::create($input);
                        }

                    }else{
                        ChallanList::where('challan_no',$value['challan_no'])->delete();
                        foreach ($value['items'] as $key => $val) {
                            $input = [
                                "customer_name"=> $value['customer_name'],
                                "mobile"=>$value['mobile'],
                                "date" => date('Y-m-d', strtotime($value['date'])),
                                "challan_no"=> $value['challan_no'],
                                "delivery_at"=>$value['delivery_at'],
                                "status"=>$value['status'],
                                "quality"=>$val['quality'],
                                "size_inch_length"=>$val['size_inch_length'],
                                "size_inch_width"=>$val['size_inch_width'],
                                "gsm"=>$val['gsm'],
                                "bdls"=> $val['bdls'],
                                "pkt_grs"=>$val['pkt_grs'],
                                "sheets"=>$val['sheets'],
                                "weight"=>$val['weight'],
                                "updated_on"=>Carbon::now()
                            ];
                            ChallanList::create($input);
                        }
                      
                    }
                }
                return $this->success('Import outside challan successfully !!', $request->all(), 200);
              
            }else{
                return $this->failure('Empty data found or Data not proper !!', 500);
            }
        } catch (\Exception $e) {
            return $this->failure('Something Went Wrong !!', $e->getMessage(), 500);
        }
    }

}
