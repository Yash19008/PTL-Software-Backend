<?php

namespace App\Http\Controllers\V1\Operations;

use App\Http\Controllers\Controller;

use App\Models\V1\Operations\StockMaster;

class StockMasterController extends Controller {

    public function query()
    {
        $query = StockMaster::select("*");
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
        return array( "id" => "id", "product_group" => "product_group" , "gsm" => "gsm", "size_inch_length" => "size_inch_length", "size_inch_width" => "size_inch_width", "size_cms_length" => "size_cms_length", "size_cms_width" => "size_cms_width", "pkt_grs_weight" => "pkt_grs_weight", "sheet" => "sheet", "bdls" => "bdls", "pkt_grs" => "pkt_grs", "pkg_mode" => "pkg_mode", "weight" => "weight", "quality" => "quality", "gwd" => "gwd", "loc" => "loc", "updated_on" => "updated_on");
    }

}
