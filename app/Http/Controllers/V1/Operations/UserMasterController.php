<?php

namespace App\Http\Controllers\V1\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Http\Requests\Operations\UserMasterRequest;
use App\Models\V1\Operations\UserMaster;
use DB;


class UserMasterController extends Controller {

    public function store(UserMasterRequest $request) {
		$user_id = UserMaster::create([
			"employee_name"=>$request->get('employee_name'),
			"password"=> \Hash::make($request->get('password')),
			"email_id"=> $request->get('email_id'),
			"userlevel"=>$request->get('userlevel'),
			"user_status"=>$request->get('user_status'),
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
    
    public function details()
    {
        $user = UserMaster::where("id", \Auth::user()->id)->first();
        if ($user) {
            if ($user->user_status == 1) {
                return $this->failure('User not active', null, 500);
            }
            return $this->success('Vendor History Last Uploaded', $user, 200);
        }
        return $this->failure('User not found', null, 500);
    }

    public function getAllVendors()
    {
        $query = UserMaster::where("userlevel", 2)->where("user_status", 0)->get();
        return $this->success('All vendors list', $query, 200);
    }


}
