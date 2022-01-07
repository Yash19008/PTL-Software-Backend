<?php

namespace App\Http\Controllers\V1\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Http\Requests\Operations\OrderMasterRequest;
use App\Models\V1\Operations\OrderMaster;
use App\Models\V1\Operations\OrderMasterView;

class OrderMasterController extends Controller {

    public function store(OrderMasterRequest $request) {
		$user_id = OrderMaster::create([
			"employee_name"=>$request->get('employee_name'),
			"password"=> \Hash::make($request->get('password')),
			"email_id"=> $request->get('email_id'),
			"userlevel"=>$request->get('userlevel'),
			"user_status"=>$request->get('user_status'),
			"updated_dt"=> Carbon::now(),
			"updated_by"=> \Auth::user()->id
        ])->id;
        return $this->success('OrderMaster Response Submitted Successully !!', null, 200);
    }

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
        $user = OrderMaster::where('id', $id)->first();
        return $this->success('OrderMaster Responses !!', $user, 200);
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

}
