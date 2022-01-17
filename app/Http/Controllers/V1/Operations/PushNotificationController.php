<?php

namespace App\Http\Controllers\V1\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\V1\Operations\PushNotificationData;
use App\Models\V1\Operations\PushNotification;
use DB;

class PushNotificationController extends Controller {

    public function query()
    {
        $query = PushNotificationData::select("*");
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
        return array( "id" => "id", "client_id" => "client_id" , "company_name" => "company_name", "mobile" => "mobile", "size_in_inch" => "size_in_inch");
    }

    public function sendsize(Request $request) {
        
        $hours = $request->get('hours');
        $utilisation = $request->get('utilisation');
		$push_not_id = PushNotification::create([
			"hours"=> $hours,
			"utilisation"=>$utilisation
        ])->id;
        
        $new_time = date("Y-m-d H:i:s", strtotime("-$hours minutes"));

        $list=DB::select("SELECT s.id, s.company_name, s.width, s.heigth, s.gsm, s.size_in_inch, s.timestamp, 
        cm.id as client_id, cm.company_name, cm.mobile, cm.active, cm.stock_active, cm.oneSignalUserId, cm.oneSignalTokenId
        FROM search_history_master as s
        LEFT JOIN customer_master as cm ON cm.company_name = s.company_name
        WHERE s.timestamp >= '$new_time'
        GROUP BY s.company_name, s.size_in_inch ORDER BY s.id DESC");

        for($i=0; $i<count($list); $i++) {
            $record = $list[$i];
            
            $width = $record->width;
            $heigth = $record->heigth;
            $gsm = $record->gsm;
            $size_in_inch = $record->size_in_inch;
            
            $active = $record->active;
            $stock_active = $record->stock_active;
            $oneSignalUserId = $record->oneSignalUserId;
            
            $stock_data = $this->find_from_stock($width, $heigth, $gsm, $utilisation);
            if( count($stock_data) > 0 ) {
                if(!empty($oneSignalUserId) && $active == 1 && $stock_active == 0) {
                    $message = "Your search size $size_in_inch is having better options available now !!";
                    $send = $this->send_notification($oneSignalUserId, $record, $message);
                    
                    $send = json_decode($send);
                    
                    $onesignal_ref_id = $send->id;
                    
                    
                    if(!empty($onesignal_ref_id)) {
                        $push_not_data = array();
                        $push_not_data['push_not_id'] = $push_not_id;
                        $push_not_data['client_id'] = $record->client_id;
                        $push_not_data['company_name'] = $record->company_name;
                        $push_not_data['mobile'] = $record->mobile;
                        $push_not_data['onesignal_id'] = $oneSignalUserId;
                        $push_not_data['size_in_inch'] = $size_in_inch;
                        $push_not_data['record'] = json_encode($record);
                        $push_not_data['onesignal_ref_id'] = $send->id;
                        $push_not_data['message'] = $message;
                        
                        PushNotificationData::create($push_not_data);
                    }
                }
            } else {
                // NO records found !!
            }
            
        }
        return $this->success('Notification Sent Successfully', null, 200);
    } 
    
    
    public function find_from_stock($width, $heigth, $gsm, $utilisation) {
        $data=DB::select("
        
        SELECT stock.quality, gsm, dup.utiliz, dup.id, 
        CONCAT(size_inch_length,' X ',size_inch_width) as Size_INCH,size_inch_length ,size_inch_width,
        TRUNCATE(size_inch_length/".$heigth." ,0) AS LEN_UPS ,
        TRUNCATE(size_inch_width/".$width." ,0) AS WID_UPS ,
        TRUNCATE(TRUNCATE(size_inch_length/".$heigth.",0)*TRUNCATE(size_inch_width/".$width.",0),0) AS total_ups,
        TRUNCATE((pkt_grs_weight/sheet*100),1)  as  sheet_weight,
        (sheet*pkg_mode) as bundle
        
        FROM stock
        
        INNER JOIN
        (SELECT id, (ROUND(((".$heigth."*".$width.")/(size_inch_length*size_inch_width))*(TRUNCATE(TRUNCATE(size_inch_length/".$heigth.",0)*TRUNCATE(size_inch_width/".$width.",0),0)) * 100)) as utiliz
        FROM stock
        HAVING  utiliz >= $utilisation AND utiliz <= 100 ORDER BY utiliz DESC, size_inch_length ASC) dup
        ON stock.id = dup.id
        group by quality, gsm
        ORDER BY utiliz DESC,gsm    ");
        
        return $data;
        
    }
    
    
    public function send_notification($oneSignalUserId, $data, $message) {
        
        // print_r($data);exit;
        $headings = array(
            "en" => 'New Stock Available !!',
        );
        $content = array(
            "en" => $message,
        );
        $buttons = array([
                            "id"=> "order_now", 
                            "text"=> "Click Here to Order Now", 
                            "icon"=> "ic_menu_share"]);
        
        // $url = env('FRONT_URL', 'localhost:8000') . '/#/task/detail/' . ($modelObject->id ?? null);
        // $url = 'localhost:8000';
        
        $fields = array(
            'app_id' => "d5689a9a-30c3-487b-bbae-684ea1c61d4d",
            'include_player_ids' => [$oneSignalUserId],
            'data' => array(
                "width" => $data->width,
                "height" => $data->heigth,
                "gsm" => $data->gsm,
            ),
            // 'headings' => $headings,
            'contents' => $content,
            // 'url' => $url,
            'buttons' => $buttons
        );

        $fields = json_encode($fields);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function sendmessage(Request $request) {
        
        $message = $request->get('message');
        $hours = 0;
        $utilisation = 0;
		$push_not_id = PushNotification::create([
			"message"=> $message,
			"hours"=> $hours,
			"utilisation"=>$utilisation
        ])->id;
        
        $list=DB::select("SELECT c.*
        FROM customer_master as c
        WHERE oneSignalUserId IS NOT NULL");
        
        $send = $this->send_broadcast($message);
        $send = json_decode($send);
        for($i=0; $i<count($list); $i++)
        {
            $record = $list[$i];
    		$onesignal_ref_id = $send->id;
    		
    		if(!empty($onesignal_ref_id))
    		{
    			$push_not_data = array();
    			$push_not_data['push_not_id'] = $push_not_id;
    			$push_not_data['client_id'] = $record->id;
    			$push_not_data['company_name'] = $record->company_name;
    			$push_not_data['mobile'] = $record->mobile;
    			$push_not_data['onesignal_id'] = $record->oneSignalUserId;
    			$push_not_data['record'] = json_encode($record);
    			$push_not_data['onesignal_ref_id'] = $send->id;
    			$push_not_data['message'] = $message;
    			
                PushNotificationData::create($push_not_data);
    		}
        }
        
        return $this->success('Notification Sent Successfully', null, 200);
    }
    
    
    public function send_broadcast($message) {
        $headings = array(
            "en" => 'Notification !!',
        );
        $content = array(
            "en" => $message,
        );
        
        $fields = array(
            'app_id' => "d5689a9a-30c3-487b-bbae-684ea1c61d4d",
            'contents' => $content,
            'included_segments' => array(
                        'All'
            ),
        );

        $fields = json_encode($fields);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                'Content-Type: application/json; charset=utf-8',
                                'Authorization: Basic MjM2YmJhMjctM2RhMi00N2ZmLWE4MzAtZDg0YzdjYjZmOTA3'
                    ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
    
    public function push_notification_list(Request $request) {
        
        $mobile = $request->get('mobile');
        
        $data_record=DB::select("SELECT *, DATE_FORMAT(timestamp, '%d-%m-%Y %H:%i:%S') as timestamp FROM `push_not_data` WHERE `mobile` = $mobile ORDER BY id DESC limit 5");
       
        $output['data'] = $data_record;
        $output['message'] = 'Size in Inch Detail !!';
        $output['status'] = 'success';
        
        return response()->json($output, 200);
        
    }

}
