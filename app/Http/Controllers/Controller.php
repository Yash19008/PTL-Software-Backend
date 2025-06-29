<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use App\Models\V1\Operations\StockMaster;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function success($message, $data, $code = 200) {
        return response()->json(['status' => 'success', 'message'=> __($message), 'data'=> $data], $code);
    }

    public function failure($message, $data = null, $code = 200) {
        return response()->json(['status' => 'error', 'message'=> __($message), 'data'=> $data], $code);
    }

    public function search($query, $tableColumns = [], $search = [], $operation = 'LIKE') {
		if(!empty($search) && !empty($tableColumns))
		{
			foreach($search as $key=>$searchvalue)
			{
                if ($searchvalue != '') {
                    if (strtoupper($operation) == 'LIKE') {
                        $query =  $query->where($tableColumns[$key], $operation, '%'.$searchvalue.'%');
                    } else {
                        $query =  $query->where($tableColumns[$key], $operation, $searchvalue);
                    }
                }
			}
		}
        return $query;
    }

    public function sort($query, $tableColumns = [], $sort = [], $defaultSortCol = 'name')
    {
		if(!empty($sort) && !empty($tableColumns))
		{
			$query = $query->orderBy($tableColumns[key($sort)], $sort[key($sort)]);
		} else {
			$query = $query->orderBy($defaultSortCol, 'DESC');
        }
		return $query;       
    }

    public function get_data_from_connection($connection, $type, $data) {
        $config_url = \Config::get('global.EXTERNAL_URLS.'.$connection);
        if (isset($config_url)) {
            $external_url = \Config::get('global.EXTERNAL_URLS.'.$connection).'get_data_from_database';

            \Log::info('External API Call to : '.$external_url);
            $response = Http::post($external_url, ['type' => $type, 'data' => $data]);
    
            \Log::info('Status Code is : '. $response->status());
            return $response->json();
        } else {
            \Log::info('No config url for connection : '. $connection);
        }
        return null;
    }

    public function get_data_from_database(Request $request) {
        switch($request->type) {
            case 'get_sum':
                return StockMaster::sum('weight');
            case 'getStock':
                return StockMaster::where('id', $request->data)->first();
            case 'searchSizeQuery':
                return $this->searchSizeQuery('mysql', $request->data['from'], $request->data['userlength'], $request->data['userwidth'], $request->data['lower_range'], $request->data['upper_range'], $request->data['where']);
            case 'searchSizeQueryReel':
                return $this->searchSizeQueryReel('mysql', $request->data['from'], $request->data['userlength'], $request->data['userwidth'], $request->data['lower_range_reel'], $request->data['upper_range_reel'], $request->data['where'], $request->data['gsm']);
            case 'search_new_common_query':
                return $this->search_new_common_query('mysql', $request->data['from'], $request->data['userlength'], $request->data['userwidth'], $request->data['lower_range'], $request->data['upper_range'], $request->data['where'], $request->data['sheets_result_count']);
            case 'reel_search_new_common_query':
                return $this->reel_search_new_common_query('mysql', $request->data['from'], $request->data['userlength'], $request->data['userwidth'], $request->data['reel_lower_range'], $request->data['reel_upper_range'], $request->data['where'], $request->data['reels_result_count'], $request->data['gsm']);
            case 'getUniqueQualitiesFromStock':
                return $this->getUniqueQualitiesFromStock('mysql');
        }
    }

    public function searchSizeQuery($connection, $name, $userlength, $userwidth, $lower_range, $upper_range, $where)
    {
        return \DB::connection($connection)->select("
            SELECT '" . $name . "' as company, stock.product_group, stock.quality, gsm, dup.utiliz, dup.id, 
                    CONCAT(size_inch_length,' X ',size_inch_width) as Size_INCH,size_inch_length ,size_inch_width,size_cms_length,size_cms_width, stock.gwd,
                    TRUNCATE(size_inch_length/" . $userlength . " ,0) AS LEN_UPS ,
                    TRUNCATE(size_inch_width/" . $userwidth . " ,0) AS WID_UPS ,
                    TRUNCATE(TRUNCATE(size_inch_length/" . $userlength . ",0)*TRUNCATE(size_inch_width/" . $userwidth . ",0),0) AS total_ups,
                    TRUNCATE((pkt_grs_weight/sheet*100),1)  as  sheet_weight,
                    (sheet*pkg_mode) as bundle, sheet, pkg_mode,
                    
                    SUM(sheet*pkt_grs) as total_sheet
                    
                    FROM stock
                    
                    INNER JOIN
                        (SELECT id, (ROUND(((" . $userlength . "*" . $userwidth . ")/(size_inch_length*size_inch_width))*(TRUNCATE(TRUNCATE(size_inch_length/" . $userlength . ",0)*TRUNCATE(size_inch_width/" . $userwidth . ",0),0)) * 100)) as utiliz
                        FROM stock
                        WHERE stock.gsm BETWEEN " . $lower_range . " AND " . $upper_range . "
                        HAVING  utiliz >= (SELECT op.value FROM options_master op WHERE op.option='utilization_ups_admin') AND utiliz <= 100 
                        ORDER BY utiliz DESC, size_inch_length ASC) dup
                    ON stock.id = dup.id
                    
                    " . $where . "
                    
                    group by quality, gsm, Size_INCH
                    ORDER BY utiliz DESC, size_inch_width DESC
                    ");
    }

    function searchSizeQueryReel($connection, $name, $userlength, $userwidth, $lower_range, $upper_range, $where, $gsm)
    {
        $userwidth = number_format((float)$userwidth, 2, '.', '');
        return \DB::connection($connection)->select("SELECT '" . $name . "' as company, stock.product_group, stock.quality, gsm, dup.utiliz as utilization, stock.id, stock.gwd,
        CONCAT(size_inch_length,' X '," . $userwidth . ") as Size_INCH, size_inch_length ,size_inch_width,size_cms_length,size_cms_width,
        CONCAT(size_inch_length,' X '," . $userwidth . ") as size_inch,
        TRUNCATE(size_inch_length/" . $userlength . " ,0) AS LEN_UPS ,
        TRUNCATE(size_inch_width/" . $userwidth . " ,0) AS WID_UPS ,
        TRUNCATE(TRUNCATE(size_inch_length/" . $userlength . ",0),0) AS total_ups,
            ''  as  sheet_weight,
            TRUNCATE((stock.weight/((" . $userlength . "*" . $userwidth . "*" . $gsm . "/8.2/1307.25)/144)),-2)  as  total_sheet,
            '' as bundle
            
            FROM stock
            
            
            INNER JOIN
                (SELECT id, (ROUND(((" . $userlength . ")/(size_inch_length))*(TRUNCATE(TRUNCATE(size_inch_length/" . $userlength . ",0),0)) * 100)) as utiliz
                FROM stock
                WHERE stock.gsm BETWEEN " . $lower_range . " AND " . $upper_range . "
                HAVING  utiliz >= (SELECT op.value FROM options_master op WHERE op.option='utilization_ups_admin') AND utiliz <= 100 
                ORDER BY utiliz DESC, size_inch_length ASC) dup
            ON stock.id = dup.id
            
            " . $where . " AND size_inch_width = 0.00
            
            group by quality, gsm, Size_INCH
            ORDER BY utilization DESC, size_inch_width DESC");
    }

    function search_new_common_query($connection, $name, $userlength, $userwidth, $lower_range, $upper_range, $where, $sheets_result_count)
    {
        return \DB::connection($connection)->select("SELECT '" . $name . "' as company, stock.quality, gsm, util.utilization, stock.id, stock.gwd,
            CONCAT(size_inch_length,' X ',size_inch_width) as Size_INCH, size_inch_length ,size_inch_width,size_cms_length,size_cms_width,
            CONCAT(size_inch_length,' X ',size_inch_width) as size_inch,
            TRUNCATE(size_inch_length/" . $userlength . " ,0) AS LEN_UPS ,
            TRUNCATE(size_inch_width/" . $userwidth . " ,0) AS WID_UPS ,
            TRUNCATE(TRUNCATE(size_inch_length/" . $userlength . ",0)*TRUNCATE(size_inch_width/" . $userwidth . ",0),0) AS total_ups,
            TRUNCATE((pkt_grs_weight/sheet*100),1)  as  sheet_weight,
            (sheet*pkg_mode) as bundle, sheet, pkg_mode,
            
            SUM(sheet*pkt_grs) as total_sheet
            
            FROM stock
            
            INNER JOIN
                (SELECT id, (ROUND(((" . $userlength . "*" . $userwidth . ")/(size_inch_length*size_inch_width))*(TRUNCATE(TRUNCATE(size_inch_length/" . $userlength . ",0)*TRUNCATE(size_inch_width/" . $userwidth . ",0),0)) * 100)) as utiliz
                FROM stock
                WHERE stock.gsm BETWEEN " . $lower_range . " AND " . $upper_range . " 
                HAVING  utiliz >= (SELECT op.value FROM options_master op WHERE op.option='utilization_ups') AND utiliz <= 100 
                ORDER BY utiliz DESC, size_inch_length ASC ) dup
            ON stock.id = dup.id
            
            INNER JOIN
                (SELECT id, (ROUND(((" . $userlength . "*" . $userwidth . ")/(size_inch_length*size_inch_width))*(TRUNCATE(TRUNCATE(size_inch_length/" . $userlength . ",0)*TRUNCATE(size_inch_width/" . $userwidth . ",0),0)) * 100)) as utilization
                FROM stock
                WHERE stock.gsm BETWEEN " . $lower_range . " AND " . $upper_range . " 
                HAVING  utilization >= (SELECT op.value FROM options_master op WHERE op.option='utilization_ups') AND utilization <= 100 
                ORDER BY utilization DESC, size_inch_length ASC) util
            ON stock.id = util.id
            
            " . $where . " AND weight > 0 
            
            group by quality, gsm, Size_INCH
            
            ORDER BY utilization DESC, size_inch_width DESC");
    }

    function reel_search_new_common_query($connection, $name, $userlength, $userwidth, $lower_range, $upper_range, $where, $reels_result_count, $gsm)
    {
        $userwidth = number_format((float)$userwidth, 2, '.', '');
        return \DB::connection($connection)->select("SELECT '" . $name . "' as company, stock.quality, gsm, dup.utiliz as utilization, stock.id, stock.gwd,
        CONCAT(size_inch_length,' X '," . $userwidth . ") as Size_INCH, size_inch_length ,size_inch_width,size_cms_length,size_cms_width,
        CONCAT(size_inch_length,' X '," . $userwidth . ") as size_inch,
        TRUNCATE(size_inch_length/" . $userlength . " ,0) AS LEN_UPS ,
        TRUNCATE(size_inch_width/" . $userwidth . " ,0) AS WID_UPS ,
        TRUNCATE(TRUNCATE(size_inch_length/" . $userlength . ",0),0) AS total_ups,
            ''  as  sheet_weight,
            TRUNCATE((stock.weight/((" . $userlength . "*" . $userwidth . "*" . $gsm . "/8.2/1307.25)/144)),-2)  as  total_sheet,
            '' as bundle
            
            FROM stock
            
            
            INNER JOIN
                (SELECT id, (ROUND(((" . $userlength . ")/(size_inch_length))*(TRUNCATE(TRUNCATE(size_inch_length/" . $userlength . ",0),0)) * 100)) as utiliz
                FROM stock
                WHERE stock.gsm BETWEEN " . $lower_range . " AND " . $upper_range . "
                HAVING  utiliz >= (SELECT op.value FROM options_master op WHERE op.option='utilization_ups_reel') AND utiliz <= 100 
                ORDER BY utiliz DESC, size_inch_length ASC) dup
            ON stock.id = dup.id
            
            " . $where . " AND size_inch_width = 0.00
            
            group by quality, gsm, Size_INCH
            ORDER BY utilization DESC, size_inch_width DESC");
    }

    function getUniqueQualitiesFromStock($connection) {
        return \DB::connection($connection)->select("SELECT DISTINCT quality FROM stock");
    }

}
