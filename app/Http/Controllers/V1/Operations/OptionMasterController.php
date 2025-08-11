<?php

namespace App\Http\Controllers\V1\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\V1\Operations\OptionMaster;
use App\Models\V1\Operations\PeptekStock;

class OptionMasterController extends Controller {

    public function update(Request $request, $id) {
    	$user = OptionMaster::findOrFail($id);
        $data = [
			"detail"=>$request->get('detail'),
			"value"=>$request->get('value'),
			"updated_dt"=> Carbon::now(),
			"updated_by"=> \Auth::user()->id
        ];
    	$user->update($data);
        return $this->success('OptionMaster updated successfully', $user, 200);
    }

    public function show($id) {
        $user = OptionMaster::where('id', $id)->first();
        return $this->success('OptionMaster Responses !!', $user, 200);
    }

    public function query()
    {
        $query = OptionMaster::select("*");
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
        return $this->success('OptionMaster Responses List', $query, 200);
    }

    public function allItems()
    {
        $query = OptionMaster::get();
        return $this->success('OptionMasters', $query, 200);
    }

    public function getTableColumn() {         
        return array( "id" => "id", "detail" => "detail" , "value" => "value");
    }
    
    public function onactioncall() {
        $result1 = OptionMaster::where('option', 'onaction_call')->first();
	    echo $number_no=$result1->value;
    }

    public function android_version()
    {
        $result1 = OptionMaster::where('option', 'android_version')->first();
        $output['data'] = $result1;
        $output['update_url'] = NULL;
        $output['message'] = 'Android Version !!';
        $output['status'] = 'success';
        
        return response()->json($output, 200);
    }

    public function ios_version()
    {
        $result1 = OptionMaster::where('option', 'ios_version')->first();
        $output['data'] = $result1;
        $output['update_url'] = "https://apps.apple.com/in/app/pap-tech-sales-corporation/id1546234478";
        $output['message'] = 'IOS Version !!';
        $output['status'] = 'success';
        
        return response()->json($output, 200);
    }

    public function get_challan_url(Request $request)
    {
        $company = $request->get('company');

        switch ($company) {
            case "PTL":
                $result1 = OptionMaster::where('option', 'challan_url_ptl')->first();
                break;
            case "Pap Tech":
                $result1 = OptionMaster::where('option', 'challan_url_paptech')->first();
                break;
            case "Paper Hub":
            case 'Pap Tech - Ahmedabad':
                $result1 = OptionMaster::where('option', 'challan_url_paperhub')->first();
                break;
            case "Parekh":
                $result1 = OptionMaster::where('option', 'challan_url_parekh')->first();
                break;
            default:
                $result1 = OptionMaster::where('option', 'challan_url_test')->first();
                break;
        }
        $output['data'] = $result1;
        $output['message'] = 'Challan URL !!';
        $output['status'] = 'success';

        return response()->json($output, 200);
    }
}
