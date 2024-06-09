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
        $data_record['contact_us']['addess'] = 'Address: F-60 1ST Floor, Xth Central Mall, Near D-mart, Mahavir Nagar, Kandivali (West), Mumbai - 400 067';
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
                        "bank_name"=>'HDFC Bank Ltd.',
                        "account_name"=>'PapTech Corp Pvt Ltd',
                        "account_number"=>'502 00 091 400 010',
                        "neft_code"=>'HDFC 00 00 667',
                        "branch_location"=>'Thakur House, Akurli Road, Kandivali East',
                        "bank_logo"=>'http://admin.paptechsales.com/image/hdfc_new.png'
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
