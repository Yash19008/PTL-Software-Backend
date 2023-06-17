<?php

namespace App\Http\Controllers\V1\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Http\Requests\Operations\CustomerMasterRequest;
use App\Models\V1\Operations\CustomerMaster;
use App\Models\V1\Operations\StockColumns;

class CustomerMasterController extends Controller {

    public function store(CustomerMasterRequest $request) {
		$customer_id = CustomerMaster::create([
			"company_name"=> $request->get('company_name'),
			"client_name"=>$request->get('client_name'),
			"email"=>$request->get('email'),
			"mobile"=>$request->get('mobile'),
			"active"=>$request->get('active'),
			"stock_active"=>$request->get('stock_active'),
			"password"=> md5($request->get('password')),
			"updated_dt"=> Carbon::now()
        ])->id;

        StockColumns::create([
			"customer_id"=> $customer_id,
			"quality"=>$request->get('quality') == 'true' ? 'Yes' : 'No',
			"gsm"=>$request->get('gsm') == 'true' ? 'Yes' : 'No',
			"size_inch"=>$request->get('size_inch') == 'true' ? 'Yes' : 'No',
			"total_ups"=>$request->get('total_ups') == 'true' ? 'Yes' : 'No',
			"utilization"=>$request->get('utilization') == 'true' ? 'Yes' : 'No',
			"utilization"=>$request->get('utilization') == 'true' ? 'Yes' : 'No',
			"bundle"=>$request->get('bundle') == 'true' ? 'Yes' : 'No',
			"total_sheet"=>$request->get('total_sheet') == 'true' ? 'Yes' : 'No',
			"gwd"=>$request->get('gwd') == 'true' ? 'Yes' : 'No'
        ]);

        $data = [
			"stock_active" => $request->get('stock_active'),
			"password"=> md5($request->get('password'))
        ];
        CustomerMaster::where('mobile', $request->get('mobile'))->update($data);

        return $this->success('CustomerMaster Response Submitted Successully !!', null, 200);
    }

    public function update(CustomerMasterRequest $request, $id) {
    	$user = CustomerMaster::findOrFail($id);
        $data = [
			"company_name"=> $request->get('company_name'),
			"client_name"=>$request->get('client_name'),
			"email"=>$request->get('email'),
			"mobile"=>$request->get('mobile'),
			"active"=>$request->get('active'),
			"stock_active"=>$request->get('stock_active'),
			"updated_dt"=> Carbon::now()
        ];

        if ($request->get('password')) {
            $data['password'] = md5($request->get('password'));
        }

    	$user->update($data);

    	$stockcolumns = StockColumns::where('customer_id', $id);
        $data = [
			"quality"=>$request->get('quality') == 'true' ? 'Yes' : 'No',
			"gsm"=>$request->get('gsm') == 'true' ? 'Yes' : 'No',
			"size_inch"=>$request->get('size_inch') == 'true' ? 'Yes' : 'No',
			"total_ups"=>$request->get('total_ups') == 'true' ? 'Yes' : 'No',
			"utilization"=>$request->get('utilization') == 'true' ? 'Yes' : 'No',
			"utilization"=>$request->get('utilization') == 'true' ? 'Yes' : 'No',
			"bundle"=>$request->get('bundle') == 'true' ? 'Yes' : 'No',
			"total_sheet"=>$request->get('total_sheet') == 'true' ? 'Yes' : 'No',
			"gwd"=>$request->get('gwd') == 'true' ? 'Yes' : 'No'
        ];
    	$stockcolumns->update($data);

        $data = [
			"stock_active" => $request->get('stock_active')
        ];
        if ($request->get('password')) {
            $data['password'] = md5($request->get('password'));
        }
        CustomerMaster::where('mobile', $request->get('mobile'))->update($data);
        
        return $this->success('CustomerMaster updated successfully', $user, 200);
    }

    public function show($id) {
        $user = CustomerMaster::where('id', $id)->first();
        $user->stock_columns = StockColumns::where('customer_id', $id)->first();
        return $this->success('CustomerMaster Responses !!', $user, 200);
    }

    public function query()
    {
        $query = CustomerMaster::select("*");
        return $query;
    }

    public function index()
    {
        $limit = \Config::get('global.PAGINATE.limit');
        $query = $this->query();
        $tablesColumns = $this->getTableColumn();
        $query = $this->search($query, $tablesColumns, \Request::get('search'));
        $query = $this->sort($query, $tablesColumns, \Request::get('sort'), 'company_name');
        $query = $query->paginate($limit);
        return $this->success('CustomerMaster Responses List', $query, 200);
    }

    public function getTableColumn()
    {         
        return array( "company_name" => "company_name", "client_name" => "client_name" , "email" => "email", "mobile" => "mobile", "active" => "active", "stock_active" => "stock_active", "otp" => "otp");
    }

}
