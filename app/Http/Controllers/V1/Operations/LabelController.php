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
        // $data_record['contact_us']['addess'] = 'Address: Gala No.4, Bldg No.1, Shiv Shankar Indl Estate, Behind Burma Shell Petrol Pump, Village Valiv, Vasai(E) - 401208';
        $data_record['contact_us']['addess'] = 'Plot No.10, Behind Sethia Industrial Park, Near Golden Chariot Hotel, Off Western Express Highway, Vasai (E), Thane - 401208';
        $data_record['contact_us']['mobile'] = 'Tel: 810 873 1234 / 740 047 1234';
        $data_record['contact_us']['email'] = 'papertradelink@gmail.com';
        
        
        $data_record['order_status']['contact_number'] = '7400471233';
        
        $output['data'] = $data_record;
        $output['message'] = 'Labels !!';
        $output['status'] = 'success';
        
        return response()->json($output, 200);
    }

    
    public function bank_details() {
        $data=array(
                    [    "bank_name"=>'HDFC Bank',
                        "account_name"=>'Paper Trade Link',
                        "account_number"=>'06672 00 00 00 750',
                        "neft_code"=>'HDFC 0000 667',
                        "branch_location"=>'Akruli Road, Kandivali East',
                        // "bank_logo"=>'http://papertradelink.com/old_code/assets/global/custom/hdfc_logo.png'
                        "bank_logo"=>'http://papertradelink.com/image/hdfc_new.png'
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
        $data_record['qr_code_details'] = "http://papertradelink.com/image/ptl_qr_code.jpeg";
        
        $output['data'] = $data_record;
        $output['message'] = 'Size in Inch Detail !!';
        $output['status'] = 'success';
        
        return response()->json($output, 200);
    }

}
