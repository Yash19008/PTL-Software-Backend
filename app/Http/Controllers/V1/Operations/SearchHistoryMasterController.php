<?php

namespace App\Http\Controllers\V1\Operations;

use App\Http\Controllers\Controller;

use App\Models\V1\Operations\SearchHistoryMaster;

class SearchHistoryMasterController extends Controller {

    public function query()
    {
        $query = SearchHistoryMaster::select("*");
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
        return array( "id" => "id", "company_name" => "company_name", "client_name" => "client_name" , "email" => "email", "mobile" => "mobile", "width" => "width", "heigth" => "heigth", "size_in_inch" => "size_in_inch", "gsm" => "gsm", "product_group" => "product_group", "timestamp" => "timestamp");
    }

}
