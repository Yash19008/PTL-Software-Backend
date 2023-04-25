<?php

namespace App\Http\Controllers\V1\Operations;
use Illuminate\Http\Request;
use DB;

use App\Http\Controllers\Controller;
use App\Models\V1\Operations\OrderMaster;
use App\Models\V1\Operations\ChallanList;
use App\Models\V1\Operations\Outstanding;

class DashboardController extends Controller {

    public function sales_tile(Request $request) {
        $start = $request->get('start');
        $end = $request->get('end');

        $query = ChallanList::selectRaw("*, SUM(weight) as total_weight, DATE_FORMAT(date, '%d/%m/%Y') AS formatted_date")->whereRaw(
        "(date >= ? AND date <= ?)", 
        [
            $start ." 00:00:00", 
            $end ." 23:59:59"
        ])->groupBy(DB::raw('DATE(date)'))->orderBy('date', 'DESC')->get();
        
        return response()->json($query, 200);
    }
    
    public function fastest_selling_tile(Request $request) {
        $query = DB::select("SELECT `quality`, gsm, SUM(`weight`) as total_weight, `size_inch_length`, `size_inch_width`
        FROM `challan_list`
        GROUP BY `quality`, `gsm`, `size_inch_length`, `size_inch_width`
        ORDER BY total_weight DESC");
        
        return response()->json($query, 200);
    }
    
    public function outstanding_tile(Request $request) {
        $start = $request->get('start');
        $end = $request->get('end');

        $query = Outstanding::selectRaw("*, SUM(balance) as total_balance")->whereRaw(
        "(date >= ? AND date <= ?)", [$start,  $end])
        ->groupBy('customer_name')->orderBy('total_balance', 'DESC')->take(50)->get();
        
        return response()->json($query, 200);
    }
    
    public function customer_search_history_tile(Request $request) {
        $date = $request->get('date');
        $query = DB::select("SELECT company_name, size_in_inch, gsm, product_group, COUNT(*) count FROM search_history_master WHERE DATE(timestamp) >= '".$date."' AND DATE(timestamp) <= '".$date."' GROUP BY company_name, size_in_inch, gsm, product_group HAVING COUNT(*) > 0 ORDER BY company_name asc, count desc, size_in_inch asc;");
        return response()->json($query, 200);
    }
}
