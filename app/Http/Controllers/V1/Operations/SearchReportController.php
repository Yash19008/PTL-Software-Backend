<?php

namespace App\Http\Controllers\V1\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

use App\Models\V1\Operations\SearchReportExport;

class SearchReportController extends Controller {

    public function index(Request $request)
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $wise = $request->get('wise');

        if ($wise == 'Customer') {
            $query = DB::select("SELECT company_name, size_in_inch, gsm, product_group, COUNT(*) count FROM search_history_master WHERE DATE(timestamp) >= '".$from."' AND DATE(timestamp) <= '".$to."' GROUP BY company_name, size_in_inch, gsm, product_group HAVING COUNT(*) > 0 ORDER BY company_name asc, count desc, size_in_inch asc;");
        } else {
            $query = DB::select("SELECT size_in_inch, gsm, product_group, COUNT(*) count FROM search_history_master WHERE DATE(timestamp) >= '".$from."' AND DATE(timestamp) <= '".$to."' GROUP BY size_in_inch, gsm, product_group HAVING COUNT(*) > 0 ORDER BY count desc, size_in_inch asc");
        }
        try {
            $tasksExport = new SearchReportExport($query, $wise);
            return \Excel::download($tasksExport, 'contacts.xlsx');
        } catch (\Exception $e) {
            \Log::error($e);
        }

    }

}
