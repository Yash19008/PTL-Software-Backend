<?php

namespace App\Http\Controllers\V1\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Http\Requests\Operations\UserMasterRequest;
use App\Models\V1\Operations\UserMaster;
use DB;
use App\Models\V1\Operations\CustomerMaster;


class UserMasterController extends Controller {

    public function store(UserMasterRequest $request) {
		$user_id = UserMaster::create([
			"employee_name"=>$request->get('employee_name'),
			"password"=> \Hash::make($request->get('password')),
			"email_id"=> $request->get('email_id'),
			"userlevel"=>$request->get('userlevel'),
			"user_status"=>$request->get('user_status'),
			"menus"=>$request->get('menus'),
			"updated_dt"=> Carbon::now(),
			"updated_by"=> \Auth::user()->id
        ])->id;
        return $this->success('UserMaster Response Submitted Successully !!', null, 200);
    }

    public function update(UserMasterRequest $request, $id) {
    	$user = UserMaster::findOrFail($id);
        $data = [
			"employee_name"=>$request->get('employee_name'),
			"email_id"=>$request->get('email_id'),
			"userlevel"=>$request->get('userlevel'),
			"user_status"=>$request->get('user_status'),
			"menus"=>$request->get('menus'),
			"updated_dt"=> Carbon::now(),
			"updated_by"=> \Auth::user()->id
        ];

        if ($request->get('password')) {
            $data['password'] = \Hash::make($request->get('password'));
        }

    	$user->update($data);

        return $this->success('UserMaster updated successfully', $user, 200);
    }

    public function show($id) {
        $user = UserMaster::where('id', $id)->first();
        return $this->success('UserMaster Responses !!', $user, 200);
    }

    public function query()
    {
        // $query = UserMaster::select("*")
        //     ->leftJoin('section_update_logs as sul', function ($join) use ($emp_id) {
        //         $join->on('es.id', '=', 'sul.section_id')
        //             ->where('sul.emp_id', $emp_id);
        //     })

        $query = DB::table("usermaster as users")
            ->select('users.*', DB::raw('DATE_FORMAT(MAX(history.created_at), \'%d/%m/%Y %H:%i:%S\') as last_stock_upload'))
            ->leftJoin('vendor_upload_history as history', function ($join) {
                $join->on('users.id', '=', 'history.user_id');
            })
            ->groupBy('users.id');
        return $query;
    }

    public function index()
    {
        $limit = \Config::get('global.PAGINATE.limit');
        $query = $this->query();
        $tablesColumns = $this->getTableColumn();
        $query = $this->search($query, $tablesColumns, \Request::get('search'));
        $query = $this->sort($query, $tablesColumns, \Request::get('sort'), 'email_id');
        $query = $query->paginate($limit);
        return $this->success('UserMaster Responses List', $query, 200);
    }

    public function getTableColumn()
    {         
        return array( "employee_name" => "employee_name", "email_id" => "email_id" , "userlevel" => "userlevel", "updated_dt" => "updated_dt", "updated_by" => "updated_by", "user_status" => "user_status", "last_stock_upload" => "last_stock_upload");
    }
    
    private function getCustomerDetailsData($id)
    {
        $customer = DB::table('customer_master')->where('id', $id)->first();
        if (!$customer) {
            return null;
        }
        $customer = (array) $customer;
        unset($customer['password']);
        $customer['stock_columns'] = DB::table('stock_columns')->where('customer_id', $id)->where('is_reel', 'No')->first();
        $customer['stock_columns_reel'] = DB::table('stock_columns')->where('customer_id', $id)->where('is_reel', 'Yes')->first();
        $customer['selectedProducts'] = DB::table('customer_product_link')->where('customer_id', $id)->pluck('product_group_id')->toArray();
        $customer['selectedQualities'] = DB::table('customer_quality_link')->where('customer_id', $id)->pluck('quality_id')->toArray();
        $devices = DB::table('user_devices')->where('user_id', $id)->select('id', 'user_id', 'device_type', 'device_id', 'device_info', 'ip_address', 'user_agent', 'last_active_at', 'created_at','token')->get();
        $customer['devices'] = $devices->map(function ($row) {
            $item = (array) $row;
            if (!empty($item['device_info']) && is_string($item['device_info'])) {
                $item['device_info'] = json_decode($item['device_info'], true) ?? $item['device_info'];
            }
            return $item;
        })->toArray();
        return $customer;
    }

    public function details(Request $request)
    {
        $user = \Auth::user();
        
        if (!$user) {
            return $this->failure('User not found', null, 401);
        }

        $isAdmin = ($user instanceof UserMaster);
        
        if ($isAdmin && $user->user_status == 1) {
            return $this->failure('User not active', null, 500);
        }

        $user->is_admin_menu = $isAdmin;
        $message = $isAdmin ? 'Vendor History Last Uploaded' . $user->id : 'Customer Details';
        return $this->success($message, $user, 200);
    }

    public function customerDetails($id)
    {
        $customer = $this->getCustomerDetailsData($id);
        if (!$customer) {
            return $this->failure('Customer not found', null, 404);
        }
        $customer['is_admin_menu'] = false;
        return $this->success('Customer Details', $customer, 200);
    }

    public function getAllVendors()
    {
        $query = UserMaster::where("userlevel", 2)->where("user_status", 0)->get();
        return $this->success('All vendors list', $query, 200);
    }
    

}
