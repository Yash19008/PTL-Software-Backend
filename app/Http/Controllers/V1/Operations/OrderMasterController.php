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
use App\Models\V1\Operations\StockMaster;
use DB;

class OrderMasterController extends Controller
{

    public function update(OrderMasterRequest $request, $id)
    {
        $order = OrderMaster::findOrFail($id);

        if (isset($order->challan_id)) {
            $order = OrderMaster::where('challan_id', $order->challan_id);
        }

        $data = [
            "challan_number" => $request->get('challan_number'),
            "status" => $request->get('status'),
            "updated_dt" => Carbon::now(),
            "update_by" => \Auth::user()->id
        ];
        $order->update($data);
        return $this->success('OrderMaster updated successfully', $order, 200);
    }

    public function show($id)
    {
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
        return array("id" => "id", "date" => "date", "group_id" => "group_id", "company_name" => "company_name", "quality" => "quality", "size_inch_length" => "size_inch_length", "size_inch_width" => "size_inch_width", "gsm" => "gsm", "qty" => "qty", "delivery_at" => "delivery_at", "status" => "status", "challan_number" => "challan_number", "gwd" => "gwd", "company" => "company", "is_reel" => "is_reel");
    }

    public function find_company_list(Request $request)
    {
        $customer_id = $request->get('customer_id');
        $mobile = $request->get('mobile');

        try {
            if (!empty($customer_id)) {
                $where = array("id" => $customer_id);
            }
            if (!empty($mobile)) {
                $where = array("mobile" => $mobile);
            }

            $comany_name = CustomerMaster::where($where)->where('active', "1")->get();
            // remove stdclass from $data_record
            $data_record = json_decode(json_encode($comany_name), true);

            if ($data_record) {

                $output['data'] = $data_record;
                $output['message'] = 'Customer Details !!';
                $output['status'] = 'success';
            } else {
                $output['message'] = 'Customer not found !!';
                $output['status'] = 'error';
            }
        } catch (\Exception $e) {
            $output['message'] = 'Customer not found !!';
            $output['status'] = 'error';
        }

        return response()->json($output, 200);
    }


    public function add_order(Request $request)
    {

        $historyID = null;
        try {
            $customer = CustomerMaster::where("id", $request->get('customer_id'))->where('active', "1")->first();
            $customer_ids = CustomerMaster::where("mobile", $customer->mobile)->pluck('id')->toArray();
            $search_history_id = $request->get('search_history_id');
            if (isset($search_history_id)) {
                $historyID = $request->get('search_history_id');
            } else {
                $history = SearchHistoryMaster::whereIn("customer_id", $customer_ids)->orderBy('timestamp', 'DESC')->first();
                $historyID = $history->id;
            }
        } catch (\Exception $e) {
            \Log::error("Fetch Search history issue for customer " . $request->get('customer_id'));
            \Log::error($e);
        }
        $data = array(
            "date" => date('Y-m-d H:i:s'),            
            "group_id" => $request->get('group_id') ?? NULL,
            "cust_id" => $request->get('customer_id'),
            "quality" => $request->get('qual'),
            "size_inch_length" => $request->get('len'),
            "size_inch_width" => $request->get('wed'),
            "gsm" => $request->get('gsm'),
            "product_group" => $request->get('product_group'),
            "qty" => $request->get('qty'),
            "delivery_at" => $request->get('deliv'),
            "gwd" => $request->get('gwd'),
            "last_searched_id" => $historyID,
            "updated_dt" => date('Y-m-d H:i:s'),
            "update_by" => $request->get('customer_id'),
            "is_reel" => $request->get('is_reel') ?? 'No',
            "platform" => $request->get('platform') ?? 'mobile'
        );


        try {
            if ($request->get('stock_id') && $request->get('company')) {
                switch ($request->get('company')) {
                    case 'PTL':
                        $stock = $this->get_data_from_connection('ptl_connection', 'getStock', $request->get('stock_id'));
                        break;
                    case 'Paper Hub':
                    case 'Pap Tech - Ahmedabad' : 
                        $stock = $this->get_data_from_connection('paper_hub_connection', 'getStock', $request->get('stock_id'));
                        break;
                    case 'Parekh':
                        $stock = $this->get_data_from_connection('parekh_connection', 'getStock', $request->get('stock_id'));
                        break;
                    default:
                        $stock = $this->getStock('mysql', $request->get('stock_id'));
                }

                $data["company"] = $request->get('company');
                if ($stock) {
                    $data["pkt_grs_weight"] = $stock->pkt_grs_weight;
                    $data["sheet"] = $stock->sheet;
                    $data["pkg_mode"] = $stock->pkg_mode;
                    $data["pkt_grs"] = $stock->pkt_grs;
                    $data["loc"] = $stock->loc;
                    $data["bdls"] = $stock->bdls;
                    $data["size_cms_length"] = $stock->size_cms_length;
                    $data["size_cms_width"] = $stock->size_cms_width;
                }
            }
        } catch (\Exception $e) {
            \Log::error("Fetch Search history issue for customer " . $request->get('customer_id'));
            \Log::error($e);
        }


        $id = OrderMaster::create($data)->id;

        $date = date('Y-m-d H:i:s');

        // sending notification of new order received          
        // $this->SendNotification($data, $id, $date);

        $output['data'] = $id;
        $output['message'] = 'Record added successfully !!';
        $output['status'] = 'success';
        return response()->json($output, 200);
    }

    public function getStock($connection, $stock_id)
    {
        return \DB::connection($connection)->select("SELECT * FROM stock WHERE id=" . $stock_id)[0];
    }

    public function addChallan(Request $request)
    {
        try {
            OrderMaster::whereIn('id', $request->order_ids)->update(['challan_number' => $request->challan_number, 'challan_id' => $request->challan_id, 'status' => 'B']);
        } catch (\Exception $e) {
            \Log::error($e);
        }

        $output['data'] = 1;
        $output['message'] = 'Record updated successfully !!';
        $output['status'] = 'success';
        return response()->json($output, 200);
    }

    public function get_orders_list(Request $request)
    {
        $whr = " ";
        $customerId = $request->get('customerId');
        if ($customerId) {
            $whr = " cust_id='" . $customerId . "'";
        }
        //fetch challan records from challan_list table with respect mobile no.
        $data_record = DB::select("SELECT *, date_format(date(date),'%d-%m-%Y') as Date_order FROM order_master WHERE " . $whr . " and date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) ORDER BY date desc");
        if (count($data_record) > 0) {
            $output['data'] = $data_record;
            $output['message'] = 'Orders List !!';
            $output['status'] = 'success';
        } else {
            $output['message'] = 'No record found !!!';
            $output['status'] = 'error';
        }
        return response()->json($output, 200);
    }

    public function get_order(Request $request)
    {
        $whr = " ";
        $id = $request->get('id');
        if ($id) {
            $whr = " id='" . $id . "'";
        }
        //fetch challan records from challan_list table with respect mobile no.
        $data_record = DB::select("SELECT *, date_format(date(date),'%d-%m-%Y') as Date_order FROM order_master WHERE " . $whr . " ORDER BY date desc");
        if (count($data_record) > 0) {
            $output['data'] = $data_record;
            $output['message'] = 'Order Details !!';
            $output['status'] = 'success';
        } else {
            $output['message'] = 'No record found !!';
            $output['status'] = 'error';
        }
        return response()->json($output, 200);
    }

    public function get_search_history(Request $request)
    {
        $id = $request->get('id');
        $data_record = SearchHistoryMaster::where("id", $id)->first();
        if ($data_record) {
            $output['data'] = $data_record;
            $output['message'] = 'Order Details !!';
            $output['status'] = 'success';
        } else {
            $output['data'] = null;
            $output['message'] = 'No record found !!';
            $output['status'] = 'error';
        }
        return response()->json($output, 200);
    }

    public function update_quantity(Request $request)
    {
        try {
            $updateObj = ['qty' => $request->quantity];
            if ($request->delivery_at != '' && $request->delivery_at != null) {
                $updateObj['delivery_at'] = $request->delivery_at;
            }
            OrderMaster::where('id', $request->order_id)->update($updateObj);
        } catch (\Exception $e) {
            \Log::error($e);
        }

        $output['data'] = 1;
        $output['message'] = 'Record updated successfully !!';
        $output['status'] = 'success';
        return response()->json($output, 200);
    }

    public function cancel_order(Request $request)
    {
        try {
            OrderMaster::where('id', $request->order_id)->update(['status' => 'C']);
        } catch (\Exception $e) {
            \Log::error($e);
        }

        $output['data'] = 1;
        $output['message'] = 'Record updated successfully !!';
        $output['status'] = 'success';
        return response()->json($output, 200);
    }
}
