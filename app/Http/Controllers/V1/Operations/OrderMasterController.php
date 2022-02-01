<?php

namespace App\Http\Controllers\V1\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Http\Requests\Operations\OrderMasterRequest;
use App\Models\V1\Operations\OrderMaster;
use App\Models\V1\Operations\OrderMasterView;
use App\Models\V1\Operations\CustomerMaster;
use App\Models\V1\Operations\SearchHistoryMaster;

class OrderMasterController extends Controller {

    public function update(OrderMasterRequest $request, $id) {
    	$user = OrderMaster::findOrFail($id);
        $data = [
			"challan_number"=>$request->get('challan_number'),
			"status"=>$request->get('status'),
			"updated_dt"=> Carbon::now(),
			"updated_by"=> \Auth::user()->id
        ];
    	$user->update($data);
        return $this->success('OrderMaster updated successfully', $user, 200);
    }

    public function show($id) {
        $order = OrderMasterView::where('id', $id)->first();
        if (!empty($order->last_searched_id) && $order->last_searched_id != null) {
            $order->history = SearchHistoryMaster::where("id", $order->last_searched_id)->first();
        } else {
            $order->history = null;
        }
        return $this->success('OrderMaster Responses !!', $order, 200);
    }

    public function query()
    {
        $query = OrderMasterView::select("*");
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
        return array( "id" => "id", "date" => "date" , "company_name" => "company_name", "quality" => "quality", "size_inch_length" => "size_inch_length", "size_inch_width" => "size_inch_width", "gsm" => "gsm", "qty" => "qty", "delivery_at" => "delivery_at", "status" => "status", "challan_number" => "challan_number");
    }
    
    public function find_company_list(Request $request) {
        $customer_id = $request->get('customer_id');
        $mobile = $request->get('mobile');
          
        try {
            if(!empty($customer_id)) {
                $where=array("id" => $customer_id);
            }
            if(!empty($mobile)) {
                $where=array("mobile" => $mobile);
            }
                
            $comany_name= CustomerMaster::where($where)->where('active', "1")->get();
            // remove stdclass from $data_record
            $data_record = json_decode( json_encode($comany_name), true);
                
            if($data_record)
            {
                
                $output['data'] = $data_record;
                $output['message'] = 'Customer Details !!';
                $output['status'] = 'success';
                
            }
            else
            {
                $output['message'] = 'Customer not found !!';
                $output['status'] = 'error';
            }
        } catch (\Exception $e) {
            $output['message'] = 'Customer not found !!';
            $output['status'] = 'error';
        }
        
        return response()->json($output, 200);
    }
    
    
    public function add_order(Request $request) {
		  
        $history = SearchHistoryMaster::where("customer_id", $request->get('customer_id'))->orderBy('timestamp', 'DESC')->first();
        $data = array(
            "date" => date('Y-m-d H:i:s'),
            "cust_id" => $request->get('customer_id'),
            "quality" => $request->get('qual'),
            "size_inch_length" => $request->get('len'),
            "size_inch_width" => $request->get('wed'),
            "gsm" => $request->get('gsm'),
            "product_group" => $request->get('product_group'),
            "qty" => $request->get('qty'),
            "delivery_at" => $request->get('deliv'),
            "last_searched_id" => $history->id,
            "updated_dt" => date('Y-m-d H:i:s'),
            "update_by" => $request->get('customer_id'),
        );
            
        $id = OrderMaster::create($data)->id;
        
        $date = date('Y-m-d H:i:s');
        
        // sending notification of new order received          
        // $this->SendNotification($data, $id, $date);
        
        $output['data'] = $id;
        $output['message'] = 'Record added successfully !!';
        $output['status'] = 'success';
        return response()->json($output, 200);
    }

}
