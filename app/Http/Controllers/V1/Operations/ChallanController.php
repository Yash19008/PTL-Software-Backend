<?php

namespace App\Http\Controllers\V1\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Http\Requests\Operations\OrderMasterRequest;
use App\Models\V1\Operations\ChallanList;

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

}
