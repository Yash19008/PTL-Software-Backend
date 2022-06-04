<?php

namespace App\Http\Controllers\V1\Operations;

use App\Http\Controllers\Controller;

use App\Models\V1\Operations\VendorHistory;

class VendorHistoryController extends Controller {

    public function lastUploaded()
    {
        $query = VendorHistory::where("user_id", \Auth::user()->id)->orderBy("id", 'DESC')->first();
        return $this->success('Vendor History Last Uploaded', $query, 200);
    }

}
