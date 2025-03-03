<?php

namespace App\Http\Controllers\V1\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

use App\Models\V1\Operations\OptionMaster;
use App\Models\V1\Operations\ProductGroup;
use App\Models\V1\Operations\StockColumns;
use App\Models\V1\Operations\CustomerMaster;
use App\Models\V1\Operations\SearchHistoryMaster;

class SearchSizeController extends Controller
{

    public function searchSize(Request $request)
    {
        $userlength = $request->get('length');
        $userwidth = $request->get('width');
        $gsm = $request->get('gsm');
        $group_name = $request->get('group_name');
        $quality = $request->get('quality');
        $company = $request->get('company');
        $company = isset($company) ? $company : '';
        $size_in_inch = $userlength . ' X ' . $userwidth;

        $option_result = OptionMaster::where('option', 'gsm_range')->first();
        $gsm_range = $option_result->value;
        $upper_range = $gsm + $gsm_range;
        $lower_range = $gsm - $gsm_range;

        $option_result = OptionMaster::where('option', 'reel_gsm_range')->first();
        $gsm_range = $option_result->value;
        $upper_range_reel = $gsm + $gsm_range;
        $lower_range_reel = $gsm - $gsm_range;

        $where = " WHERE 1=1 AND weight > 0 ";
        if ($group_name) {
            $where = $where . " AND product_group = '" . $group_name . "'";
        }
        if ($quality) {
            $where = $where . " AND quality LIKE '%" . $quality . "%'";
        }

        $data = $this->searchSizeCommon($company, $userlength, $userwidth, $lower_range, $upper_range, $lower_range_reel, $upper_range_reel, $where, $gsm);


        $option_result = OptionMaster::where('option', 'plus_minus_size_search')->first();
        if ($option_result) {
            $range = $option_result->value;

            $userWidth = number_format((float)$request->get('width'), 2, '.', '') - $range;
            $userLength = number_format((float)$request->get('length'), 2, '.', '') - $range;
            $new_data = $this->searchSizeCommon($company, $userLength, $userWidth, $lower_range, $upper_range, $lower_range_reel, $upper_range_reel, $where, $gsm);

            $data['list'] = array_merge($data['list'], $new_data['list']);
            //$data['reel_list'] = array_merge($data['reel_list'], $new_data['reel_list']);

            $data['list'] = $this->getUnique($data['list']);
            //$data['reel_list'] = $this->getUnique($data['reel_list']);
        }

        usort($data['list'], function ($a, $b) {
            return $b['utiliz'] <=> $a['utiliz'];
        });

        usort($data['reel_list'], function ($a, $b) {
            return $b['utilization'] <=> $a['utilization'];
        });

        $new_output = [];
        foreach ($data['list'] as $item) {
            if ($item['total_sheet'] > 0) {
                $new_output[] = $item;
            }
        }
        $data['list'] = $new_output;
        
        $new_output = [];
        foreach ($data['reel_list'] as $item) {
            if ($item['total_sheet'] > 0) {
                $new_output[] = $item;
            }
        }
        $data['reel_list'] = $new_output;

        return $this->success('Search Size Responses List', $data, 200);
    }

    public function searchSizeCommon($company, $userlength, $userwidth, $lower_range, $upper_range, $lower_range_reel, $upper_range_reel, $where, $gsm)
    {
        $output = [];
        $reel_output = [];

        if ($company == '' || $company == 'PTL') {
            $output = $this->searchSizeQuery('mysql', 'PTL', $userlength, $userwidth, $lower_range, $upper_range, $where);
            $reel_output = $this->searchSizeQueryReel('mysql', 'PTL', $userlength, $userwidth, $lower_range_reel, $upper_range_reel, $where, $gsm);
        }

        $admin_show_stocks_from = OptionMaster::where('option', 'admin_show_stocks_from')->first();
        $admin_show_stocks_from = explode(',', $admin_show_stocks_from->value);
        foreach ($admin_show_stocks_from as $from) {
            if ($company == '' || $company == $from) {
                switch ($from) {
                    case 'Pap Tech':
                        $result = $this->get_data_from_connection('ptsc_connection', 'searchSizeQuery', ['from' => $from, 'userlength' => $userlength, 'userwidth' => $userwidth, 'lower_range' => $lower_range, 'upper_range' => $upper_range, 'where' => $where]);
                        $output = array_merge($output, $result);
                        $result = $this->get_data_from_connection('ptsc_connection', 'searchSizeQueryReel', ['from' => $from, 'userlength' => $userlength, 'userwidth' => $userwidth, 'lower_range_reel' => $lower_range_reel, 'upper_range_reel' => $upper_range_reel, 'where' => $where, 'gsm' => $gsm]);
                        $reel_output = array_merge($reel_output, $result);
                        break;
                    case 'Paper Hub':
                        $result = $this->get_data_from_connection('paper_hub_connection', 'searchSizeQuery', ['from' => $from, 'userlength' => $userlength, 'userwidth' => $userwidth, 'lower_range' => $lower_range, 'upper_range' => $upper_range, 'where' => $where]);
                        $output = array_merge($output, $result);
                        $result = $this->get_data_from_connection('paper_hub_connection', 'searchSizeQueryReel', ['from' => $from, 'userlength' => $userlength, 'userwidth' => $userwidth, 'lower_range_reel' => $lower_range_reel, 'upper_range_reel' => $upper_range_reel, 'where' => $where, 'gsm' => $gsm]);
                        $reel_output = array_merge($reel_output, $result);
                        break;
                    case 'Parekh':
                        $result = $this->get_data_from_connection('parekh_connection', 'searchSizeQuery', ['from' => $from, 'userlength' => $userlength, 'userwidth' => $userwidth, 'lower_range' => $lower_range, 'upper_range' => $upper_range, 'where' => $where]);
                        $output = array_merge($output, $result);
                        $result = $this->get_data_from_connection('parekh_connection', 'searchSizeQueryReel', ['from' => $from, 'userlength' => $userlength, 'userwidth' => $userwidth, 'lower_range_reel' => $lower_range_reel, 'upper_range_reel' => $upper_range_reel, 'where' => $where, 'gsm' => $gsm]);
                        $reel_output = array_merge($reel_output, $result);
                        break;
                }
            }
        }

        $vendor_result = DB::select("
                SELECT usermaster.employee_name as company, stock_vendors.product_group, stock_vendors.quality, gsm, dup.utiliz, dup.id, 
                CONCAT(size_inch_length,' X ',size_inch_width) as Size_INCH,size_inch_length ,size_inch_width,size_cms_length,size_cms_width, gwd,
                TRUNCATE(size_inch_length/" . $userlength . " ,0) AS LEN_UPS ,
                TRUNCATE(size_inch_width/" . $userwidth . " ,0) AS WID_UPS ,
                TRUNCATE(TRUNCATE(size_inch_length/" . $userlength . ",0)*TRUNCATE(size_inch_width/" . $userwidth . ",0),0) AS total_ups,
                TRUNCATE((pkt_grs_weight/sheet*100),1)  as  sheet_weight,
                (sheet*pkg_mode) as bundle, sheet, pkg_mode,
                
                SUM(sheet*pkt_grs) as total_sheet
                
                FROM stock_vendors
                
                INNER JOIN
                (SELECT id, (ROUND(((" . $userlength . "*" . $userwidth . ")/(size_inch_length*size_inch_width))*(TRUNCATE(TRUNCATE(size_inch_length/" . $userlength . ",0)*TRUNCATE(size_inch_width/" . $userwidth . ",0),0)) * 100)) as utiliz
                FROM stock_vendors
                WHERE stock_vendors.gsm BETWEEN " . $lower_range . " AND " . $upper_range . "
                HAVING  utiliz >= (SELECT op.value FROM options_master op WHERE op.option='utilization_ups_admin') AND utiliz <= 100 ORDER BY utiliz DESC, size_inch_length ASC) dup
                ON stock_vendors.id = dup.id
                

                LEFT JOIN usermaster ON  usermaster.id = stock_vendors.vendor_id

                " . $where . "
                
                group by quality, gsm, Size_INCH
                ORDER BY utiliz DESC, size_inch_width DESC");
        $output = array_merge($output, $vendor_result);
        $output = json_decode(json_encode($output), true);
        usort($output, function ($a, $b) {
            return $a['utiliz'] > $b['utiliz'] ? -1 : 1;
        });

        $data_record['list'] = $output;
        $data_record['reel_list'] = $reel_output;
        $data_record = json_decode(json_encode($data_record), true);
        return $data_record;
    }

    public function getMasters()
    {
        $data_record = ProductGroup::all();

        if ($data_record) {
            $output['data']['product_group'] = $data_record;
            $output['message'] = 'Master Records !!';
            $output['status'] = 'success';
        } else {
            $output['message'] = 'Records not found !!';
            $output['status'] = 'error';
        }

        return response()->json($output, 200);
    }

    public function search_dynamic_column_wise(Request $request)
    {
        $qty = $request->get('qty');
        $searchSizeBy = $request->get('searchSizeBy');

        $widthBK = $request['width'];
        if ($searchSizeBy == 'CMS') {
            \Log::info($request['length']);
            $request['length'] = $request['length'] / 2.54;
            $request['width'] = $request['width'] / 2.54;
        }

        $customer_id = $request->get('customer_id');
        $output1['message'] = 'Size in Inch Record List !!';
        $output1['status'] = 'success';

        $result = StockColumns::where('customer_id', $customer_id)->where('is_reel', 'No')->first();

        if ($result->quality == 'Yes')   $output1['headers']['quality'] = 'Quality';
        if ($result->gsm == 'Yes')   $output1['headers']['gsm'] = 'GSM';
        if ($result->size_inch == 'Yes') {
            if ($searchSizeBy == 'CMS') {
                $output1['headers']['size_CMS'] = 'Size in CMS';
            } else {
                $output1['headers']['size_inch'] = 'Size in Inch';
            }
        }
        if ($result->total_ups == 'Yes')   $output1['headers']['total_ups'] = 'Total No. of UPS';
        if ($result->utilization == 'Yes')   $output1['headers']['utilization'] = 'Utilization';
        if ($result->sheet_weight == 'Yes')   $output1['headers']['sheet_weight'] = '100 Sheet Weight';
        if ($result->bundle == 'Yes')   $output1['headers']['bundle'] = 'Per Bundle No. of Sheet';
        if ($result->total_sheet == 'Yes')   $output1['headers']['total_sheet'] = 'Total Sheet';
        if ($result->gwd == 'Yes')   $output1['headers']['gwd'] = 'Godown';


        $product_group = $request->get('product_group');
        $searchReel = ProductGroup::where('group_name', $product_group)->where('is_reel', 'Yes')->first();
        if ($searchReel) {
            $result = StockColumns::where('customer_id', $customer_id)->where('is_reel', 'Yes')->first();

            if ($result) {
                if ($result->quality == 'Yes')   $output1['reel_headers']['quality'] = 'Quality';
                if ($result->gsm == 'Yes')   $output1['reel_headers']['gsm'] = 'GSM';
                if ($result->size_inch == 'Yes') {
                    if ($searchSizeBy == 'CMS') {
                        $output1['reel_headers']['size_CMS'] = 'Size in CMS';
                    } else {
                        $output1['reel_headers']['size_inch'] = 'Size in Inch';
                    }
                }
                if ($result->total_ups == 'Yes')   $output1['reel_headers']['total_ups'] = 'Total No. of UPS';
                if ($result->utilization == 'Yes')   $output1['reel_headers']['utilization'] = 'Utilization';
                if ($result->sheet_weight == 'Yes')   $output1['reel_headers']['sheet_weight'] = '100 Sheet Weight';
                if ($result->bundle == 'Yes')   $output1['reel_headers']['bundle'] = 'Per Bundle No. of Sheet';
                if ($result->total_sheet == 'Yes')   $output1['reel_headers']['total_sheet'] = 'Total Sheet';
                if ($result->gwd == 'Yes')   $output1['reel_headers']['gwd'] = 'Godown';

                $searchReel = isset($output1['reel_headers']);
            } else {
                $searchReel = null;
            }
        }

        $data = $this->search_new_common($request, $request->get('length'), $request->get('width'), $searchReel, 'dynamic');


        $option_result = OptionMaster::where('option', 'plus_minus_size_search')->first();
        if ($option_result) {
            $range = $option_result->value;

            $userWidth = number_format((float)$request->get('width'), 2, '.', '') - $range;
            $userLength = number_format((float)$request->get('length'), 2, '.', '') - $range;
            $new_data = $this->search_new_common($request, $userLength, $userWidth, $searchReel, 'dynamic');

            $data['list'] = array_merge($data['list'], $new_data['list']);
            //$data['reel_list'] = array_merge($data['reel_list'], $new_data['reel_list']);

            $data['list'] = $this->getUnique($data['list']);
            //$data['reel_list'] = $this->getUnique($data['reel_list']);
        }

        usort($data['list'], function ($a, $b) {
            if ($a['utilization'] == $b['utilization']) {
                return $a['size_inch_width'] <=> $b['size_inch_width'];
            }
            return $b['utilization'] <=> $a['utilization'];
        });

        usort($data['reel_list'], function ($a, $b) {
            if ($a['utilization'] == $b['utilization']) {
                return $a['size_inch_width'] <=> $b['size_inch_width'];
            }
            return $b['utilization'] <=> $a['utilization'];
        });


        $history = SearchHistoryMaster::where("customer_id", $customer_id)->orderBy('timestamp', 'DESC')->first();
        $historyID = $history->id;

        $productGroup = ProductGroup::where('group_name', $product_group)->first();
        $new_output = [];
        foreach ($data['list'] as $item) {
            $pkgMode = $productGroup->pkg_mode != 0 && $productGroup->pkg_mode != null ? $productGroup->pkg_mode : $item['pkg_mode'];
            
            $bundle = $pkgMode * $item['sheet'];
            $item['size_CMS'] = $item['size_cms_length'] . ' X ' . $item['size_cms_width'];

            $qty_as_per_size = $bundle;
            while ($qty_as_per_size < ($qty / $item['total_ups']) && $qty_as_per_size < $item['total_sheet']) {
                $qty_as_per_size = $qty_as_per_size + $bundle;
            }
            $item['qty_as_per_size'] = [];
            $val = $qty_as_per_size - $bundle;
            if ($val > 0) {
                $item['qty_as_per_size'][] = $qty_as_per_size - $bundle;
            }
            if ($qty_as_per_size <= $item['total_sheet']) {
                $item['qty_as_per_size'][] = $qty_as_per_size;
            } else if ($val <= +$qty) { // if in between last (which is greater than total_sheet) and second last
                $item['qty_as_per_size'][] = +$qty;
            }

            $item['search_history_id'] = $historyID;
            if ($item['total_sheet'] > 0) {
                $new_output[] = $item;
            }
        }
        $data['list'] = $new_output;
        
        $new_output = [];
        foreach ($data['reel_list'] as $item) {
            $item['size_CMS'] = $item['size_cms_length'] . ' X ' . number_format($widthBK, 2, '.', '');
            $item['search_history_id'] = $historyID;
            $item['total_sheet'] = $item['total_sheet'] / $item['total_ups'];
            if ($item['total_sheet'] > 0) {
                $new_output[] = $item;
            }
        }
        $data['reel_list'] = $new_output;
        
        $reel_search_threshold = OptionMaster::where('option', 'reel_search_threshold')->first();
        if ($reel_search_threshold) {
            $data['reel_search_threshold'] = $reel_search_threshold->value;
        }

        $output1['data'] = $data;
        return response()->json($output1, 200);
    }

    public function getUnique($data)
    {
        $unique = [];
        foreach ($data as $item) {
            $isUnique = true;
            foreach ($unique as $uniqueItem) {
                if ($uniqueItem['id'] == $item['id'] && $uniqueItem['company'] == $item['company']) {
                    $isUnique =  false;
                }
            }
            if ($isUnique) {
                $unique[] =  $item;
            }
        }
        return $unique;
    }


    public function search_new_common(Request $request, $userlength, $userwidth, $searchReel, $return = 'api')
    {
        $gsm = $request->get('gsm');
        $customer_id = $request->get('customer_id');
        $product_group = $request->get('product_group');
        $searched_qty = $request->get('qty');

        $size_in_inch = $userlength . ' X ' . $userwidth;

        //  print_r($this->uri->segment(5));exit;

        $result_cust = CustomerMaster::where('id', $customer_id)->first();

        // insert query for maintaining history for how search made by particular person

        $data = array(
            "customer_id" => $result_cust->id,
            "company_name" => $result_cust->company_name,
            "client_name" => $result_cust->client_name,
            "email" => $result_cust->email,
            "mobile" => $result_cust->mobile,
            "width" => $userwidth,
            "heigth" => $userlength,
            "size_in_inch" => $size_in_inch,
            "gsm" => $gsm,
            "qty" => $searched_qty,
            "product_group" => $product_group,
            "timestamp" => date('Y-m-d H:i:s')
        );

        // insert into search history
        SearchHistoryMaster::create($data);

        $option_result = OptionMaster::where('option', 'gsm_range')->first();
        $sheets_result_count = OptionMaster::where('option', 'sheets_result_count')->first()->value;
        $reels_result_count = OptionMaster::where('option', 'reels_result_count')->first()->value;
        $gsm_range = $option_result->value;
        $upper_range = $gsm + $gsm_range;
        $lower_range = $gsm - $gsm_range;

        $where = " WHERE 1=1 ";
        if ($product_group) {
            $where = $where . " AND product_group = '" . $product_group . "'";
        }

        $option_result = OptionMaster::where('option', 'reel_gsm_range')->first();
        $gsm_range = 0;
        if ($option_result) {
            $gsm_range = $option_result->value;
        }
        $reel_upper_range = $gsm + $gsm_range;
        $reel_lower_range = $gsm - $gsm_range;

        $output = [];
        $reel_output = [];
        $mobile_show_stocks_from_customer = [];
        if ($result_cust->mobile_show_stocks_from && $result_cust->mobile_show_stocks_from != '') {
            $mobile_show_stocks_from_customer = array_map('trim', explode(',', $result_cust->mobile_show_stocks_from));
        }

        $option_result = OptionMaster::where('option', 'mobile_show_stocks_from')->first();
        $mobile_show_stocks_from_settings = [];
        if ($option_result && $option_result->value && $option_result->value != '') {
            $mobile_show_stocks_from_settings = array_map('trim', explode(',', $option_result->value));
        }

        $mobile_show_stocks_from_settings[] = "PTL";

        $mobile_show_stocks_from = [];
        foreach ($mobile_show_stocks_from_customer as $value) {
            if (in_array($value, $mobile_show_stocks_from_settings)) {
                $mobile_show_stocks_from[] = $value;
            }
        }

        foreach ($mobile_show_stocks_from as $from) {
            switch ($from) {
                case 'PTL':
                    $result = $this->search_new_common_query('mysql', $from, $userlength, $userwidth, $lower_range, $upper_range, $where, $sheets_result_count);
                    $output = array_merge($output, $result);
                    if ($searchReel) {
                        $result = $this->reel_search_new_common_query('mysql', $from, $userlength, $userwidth, $reel_lower_range, $reel_upper_range, $where, $reels_result_count, $gsm);
                        $reel_output = array_merge($reel_output, $result);
                    }
                    break;
                case 'Pap Tech':
                    $result = $this->get_data_from_connection('ptsc_connection', 'search_new_common_query', ['from' => $from, 'userlength' => $userlength, 'userwidth' => $userwidth, 'lower_range' => $lower_range, 'upper_range' => $upper_range, 'where' => $where, 'sheets_result_count' => $sheets_result_count]);
                    $output = array_merge($output, $result);
                    if ($searchReel) {
                        $result = $this->get_data_from_connection('ptsc_connection', 'reel_search_new_common_query', ['from' => $from, 'userlength' => $userlength, 'userwidth' => $userwidth, 'reel_lower_range' => $reel_lower_range, 'reel_upper_range' => $reel_upper_range, 'where' => $where, 'reels_result_count' => $reels_result_count, 'gsm' => $gsm]);
                        $reel_output = array_merge($reel_output, $result);
                    }
                    break;
                case 'Paper Hub':
                    $result = $this->get_data_from_connection('paper_hub_connection', 'search_new_common_query', ['from' => $from, 'userlength' => $userlength, 'userwidth' => $userwidth, 'lower_range' => $lower_range, 'upper_range' => $upper_range, 'where' => $where, 'sheets_result_count' => $sheets_result_count]);
                    $output = array_merge($output, $result);
                    if ($searchReel) {
                        $result = $this->get_data_from_connection('paper_hub_connection', 'reel_search_new_common_query', ['from' => $from, 'userlength' => $userlength, 'userwidth' => $userwidth, 'reel_lower_range' => $reel_lower_range, 'reel_upper_range' => $reel_upper_range, 'where' => $where, 'reels_result_count' => $reels_result_count, 'gsm' => $gsm]);
                        $reel_output = array_merge($reel_output, $result);
                    }
                    break;
                case 'Parekh':
                    $result = $this->get_data_from_connection('parekh_connection', 'search_new_common_query', ['from' => $from, 'userlength' => $userlength, 'userwidth' => $userwidth, 'lower_range' => $lower_range, 'upper_range' => $upper_range, 'where' => $where, 'sheets_result_count' => $sheets_result_count]);
                    $output = array_merge($output, $result);
                    if ($searchReel) {
                        $result = $this->get_data_from_connection('parekh_connection', 'reel_search_new_common_query', ['from' => $from, 'userlength' => $userlength, 'userwidth' => $userwidth, 'reel_lower_range' => $reel_lower_range, 'reel_upper_range' => $reel_upper_range, 'where' => $where, 'reels_result_count' => $reels_result_count, 'gsm' => $gsm]);
                        $reel_output = array_merge($reel_output, $result);
                    }
                    break;
            }
        }

        $stock_vendors_result = DB::select(" SELECT usermaster.employee_name as company, stock_vendors.product_group, stock_vendors.quality, gsm, util.utilization, stock_vendors.id, stock_vendors.gwd,
            CONCAT(size_inch_length,' X ',size_inch_width) as Size_INCH,size_inch_length ,size_inch_width,size_cms_length,size_cms_width,
            CONCAT(size_inch_length,' X ',size_inch_width) as size_inch,
            TRUNCATE(size_inch_length/" . $userlength . " ,0) AS LEN_UPS ,
            TRUNCATE(size_inch_width/" . $userwidth . " ,0) AS WID_UPS ,
            TRUNCATE(TRUNCATE(size_inch_length/" . $userlength . ",0)*TRUNCATE(size_inch_width/" . $userwidth . ",0),0) AS total_ups,
            TRUNCATE((pkt_grs_weight/sheet*100),1)  as  sheet_weight,
            (sheet*pkg_mode) as bundle, sheet, pkg_mode,
            
            SUM(sheet*pkt_grs) as total_sheet
            
            FROM stock_vendors 
            
            
            INNER JOIN
                (SELECT id, (ROUND(((" . $userlength . "*" . $userwidth . ")/(size_inch_length*size_inch_width))*(TRUNCATE(TRUNCATE(size_inch_length/" . $userlength . ",0)*TRUNCATE(size_inch_width/" . $userwidth . ",0),0)) * 100)) as utilization
                FROM stock_vendors
                WHERE stock_vendors.gsm BETWEEN " . $lower_range . " AND " . $upper_range . " 
                HAVING  utilization >= (SELECT op.value FROM options_master op WHERE op.option='utilization_ups') AND utilization <= 100 
                ORDER BY utilization DESC, size_inch_length ASC) util
            ON stock_vendors.id = util.id

            LEFT JOIN usermaster ON  usermaster.id = stock_vendors.vendor_id
            
            " . $where . " AND weight > 0 
            
            GROUP BY quality, gsm, Size_INCH
            ORDER BY utilization DESC, size_inch_width DESC
        
        ");
        $output = array_merge($output, $stock_vendors_result);
        $output = json_decode(json_encode($output), true);
        usort($output, function ($a, $b) {
            return $a['utilization'] > $b['utilization'] ? -1 : 1;
        });

        $output = array_slice($output, 0, $sheets_result_count);
        $data_record['list'] = $output;
        $reel_output = array_slice($reel_output, 0, $reels_result_count);
        $data_record['reel_list'] = $reel_output;
        $data_record['stock_access'] = ($result_cust->stock_active == null) ? 0 : $result_cust->stock_active;
        $data_record = json_decode(json_encode($data_record), true);

        if ($return == 'api') {
            $output1['data'] = $data_record;
            $output1['message'] = 'Size in Inch Record List !!';
            $output1['status'] = 'success';
            return response()->json($output1, 200);
        } else {
            return $data_record;
        }
    }
}
