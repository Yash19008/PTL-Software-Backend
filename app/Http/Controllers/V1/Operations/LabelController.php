<?php

namespace App\Http\Controllers\V1\Operations;

use App\Http\Controllers\Controller;

class LabelController extends Controller {

    public function index()
    {
        
        $data_record['bank_details']['bank_details_title'] = 'Bank Details';
        $data_record['bank_details']['bank_name'] = 'Bank Name';
        $data_record['bank_details']['account_name'] = 'Account Name';
        $data_record['bank_details']['account_number'] = 'Account Number';
        $data_record['bank_details']['neft_code'] = 'RTGS/NEFT Code';
        $data_record['bank_details']['branch_location'] = 'Branch Location';
        $data_record['bank_details']['bank_logo'] = 'Branch Logo';
        
        $data_record['contact_us']['logo'] = 'Bank Name';
        $data_record['contact_us']['addess'] = 'Address: G5 GROUND FLOOR, RAGHULEELA MEGA MALL, BEHIND POISAR DEPOT, KANDIVALI (WEST), MUMBAI - 67';
        $data_record['contact_us']['mobile'] = 'Tel: 022-49615770 / 7738463018 / 7738463016';
        $data_record['contact_us']['email'] = 'accounts@paptechcorp.in';
        
        
        $data_record['order_status']['contact_number'] = '7400471233';
        
        $output['data'] = $data_record;
        $output['message'] = 'Labels !!';
        $output['status'] = 'success';
        
        return response()->json($output, 200);
    }

    
    public function bank_details() {
        $data=array(
                    [    
                        "bank_name"=>'SARASWAT CO-OPERATIVE BANK LTD.',
                        "account_name"=>'PAPTECH CORP PRIVATE LIMITED',
                        "account_number"=>'81 0000 0000 20258',
                        "neft_code"=>'SRCB 0000 341',
                        "branch_location"=>'Thakur Village, Kandivali East',
                        "bank_logo"=>'https://admin.paptechsales.com/public/image/saraswat.jpg'
                    ]
                    /*,
                    [    "bank_name"=>'ABC',
                        "account_name"=>'ABC',
                        "account_number"=>'06672 00 00 00 750',
                        "neft_code"=>'HDFC00000667',
                        "branch_location"=>'Akruli Road, Kandivali East',
                        "bank_logo"=>'http://papertradelink.com/assets/global/custom/axis_bank_logo.png'
                    ],*/
        );
                            
        $data_record['bank_details'] = $data;
        $data_record['qr_code_details'] = null;
        
        $output['data'] = $data_record;
        $output['message'] = 'Size in Inch Detail !!';
        $output['status'] = 'success';
        
        return response()->json($output, 200);
    }

}
