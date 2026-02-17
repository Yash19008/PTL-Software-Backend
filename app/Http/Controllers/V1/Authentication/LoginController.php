<?php

namespace App\Http\Controllers\V1\Authentication;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTAuth;
use App\Models\V1\Operations\UserMaster;
use App\Models\V1\Operations\CustomerMaster;
use App\Models\V1\Operations\AuditTrail;

use App\Http\Requests\Authentication\LoginRequest;

class LoginController extends Controller
{
    public function login(LoginRequest $request, JWTAuth $JWTAuth)
    {
        $credentials = $request->only(['email_id', 'password']);

        try {
            $user = UserMaster::where('email_id', $credentials['email_id'])->firstOrFail();
        } catch (\Exception $e) {
            return $this->failure('Incorrect Email or Password !', null, 500);
        }
        $user_payload = [
            'id' => (int) $user->id,
            'name' => $user->employee_name
        ];
        try {
            $token = $JWTAuth->attempt($credentials, $user_payload);
            \Log::info($token);
            if (!$token) {
                return $this->failure('Incorrect Email or Password !!', null, 500);
            }

        } catch (JWTException $e) {
            return $this->failure('Something is Wrong !!', null, 500);
        }

        if($user->userlevel == '1' && $user->username == 'admin')  {
            $user->is_admin_menu = true;
        } else {
            $user->is_admin_menu = false;
        }
        $user_payload['token'] = $token;
        $user_payload['userDetails'] = $user;
        return $this->success('Login Successully !!', $user_payload, 200);
    }
    
    public function get_details(LoginRequest $request) {
        $mobile = $request->get('mobile');
        if($mobile != '') {
            $count=CustomerMaster::where('mobile', $mobile)->count();
            if ($count >= 1) {
                $data_record = CustomerMaster::where('mobile', $mobile)->first();
                $data=array("otp"=>$data_record->otp,"active"=>$data_record->active,"count"=>$count,"password"=>$data_record->password);
            } else {
                $data=array("otp"=>NULL,"active"=>NULL,"count"=>0,"password"=>NULL);
            }
            return response()->json($data, 200);
        }  
    }
    
    public function login_new(LoginRequest $request) {
        $mobile = $request->get('mobile');
        $password = $request->get('password');
        $mobile_info = $request->get('mobile_info');
        $device_info = json_encode($request->get('device_info'));
        // echo "device_info: " . $device_info . "\n";
        
        try {
            $where=array(
                "mobile"=>$mobile,
                "password"=>md5($password),
                "active"=>'1'
            );
            $data_record = CustomerMaster::where($where)->firstOrFail();
            $data=array(
                "module"=>'Login',
                "user"=>$mobile,
                "action"=>'Login',
                "ipaddress"=>$request->get('REMOTE_ADDR'),
                "newvalue"=>$mobile_info,
            );
            AuditTrail::create($data);

            $onesignal = [
                'oneSignalUserId' => $request->get('oneSignalUserId'),
                'oneSignalTokenId' => $request->get('oneSignalTokenId'),
                'device_info' => $device_info
            ];
            if (Schema::hasColumn('customer_master', 'device_info')) {
                $onesignal['device_info'] = $request->get('device_info');
            }
            $data_record->update($onesignal);
            $output['data'] = $data_record;
            $output['message'] = 'Login Successfully Done !!';
            $output['status'] = 'success';
            return response()->json($output, 200);
        } catch (\Exception $e) {
            Log::error('LoginController@login_new failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $output['message'] = 'Something is Wrong !!';
            $output['status'] = 'error';
            return response()->json($output, 200);
        }
    }

    public function logout_new(LoginRequest $request)
    {
        $mobile = $request->get('mobile');
        if (empty($mobile)) {
            $output['message'] = 'Mobile is required';
            $output['status'] = 'error';
            return response()->json($output, 200);
        }
        try {
            $data_record = CustomerMaster::where('mobile', $mobile)->first();
            if (!$data_record) {
                $output['message'] = 'Logged out successfully';
                $output['status'] = 'success';
                return response()->json($output, 200);
            }
            $data = [
                'module' => 'Login',
                'user' => $mobile,
                'action' => 'Logout',
                'ipaddress' => $request->get('REMOTE_ADDR'),
                'newvalue' => $request->get('mobile_info', ''),
            ];
            AuditTrail::create($data);
            $clear = [
                'oneSignalUserId' => null,
                'oneSignalTokenId' => null,
            ];
            if (Schema::hasColumn('customer_master', 'device_info')) {
                $clear['device_info'] = null;
            }
            $data_record->update($clear);
            $output['data'] = $data_record;
            $output['message'] = 'Logged out successfully';
            $output['status'] = 'success';
            return response()->json($output, 200);
        } catch (\Exception $e) {
            Log::error('LoginController@logout_new failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $output['message'] = 'Something is Wrong !!';
            $output['status'] = 'error';
            return response()->json($output, 200);
        }
    }
    
    public function verify_no_new(LoginRequest $request) {
        
        $mobile_no = $request->get('mobile');
        if($mobile_no) {
            try {
                $data_record = CustomerMaster::where('mobile', $mobile_no)->first();
                $count=CustomerMaster::where('mobile', $mobile_no)->count();
                $data=array("otp"=>NULL,"active"=>$data_record->active,"count"=>$count,"password"=>$data_record->password);
                return response()->json($data, 200);
            } catch (\Exception $e) {
                \Log::info($e);
                $output['message'] = 'Something is Wrong !!';
                $output['status'] = 'error';
                return response()->json($output, 200);
            }
        }      
    }
    
    
    public function OTP()
    {
        //random otp generate
        $alphabet = '1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 6; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
       $otp=implode($pass);
       return $otp;
    }

    public function SendSMS(LoginRequest $request, $mobile_no) {
        $otp=$this->OTP();
        $data=array("otp"=>$otp,"active"=>"0","password"=>"");
    	$customerMaster = CustomerMaster::where('mobile', $mobile_no);
    	$customerMaster->update($data);
        $date=date('d-M-Y h:i:s');
        //code sending SMS to register number
        //$msg1 = "Dear Customer,your OTP No. is ".$otp." to access your mobile app of PTL registered mobile no is ".$mobile_no." generated on ".$date;
        $msg1 = $otp." is your OTP to access your mobile app of PTL generated on ".$date." Note : Registered mobile no is ".$mobile_no;
        $msg1=urlencode($msg1);
        // $url="http://www.smsgatewayhub.com/api/mt/SendSMS?APIKey=d04a4993-b918-4bca-9b91-8f42c9317e43&senderid=PTLINK&channel=2&DCS=0&flashsms=0&number=91".$mobile_no."&text=".$msg1."&route=1";
        
        $url="https://www.smsgatewayhub.com/api/mt/SendSMS?APIKey=d04a4993-b918-4bca-9b91-8f42c9317e43&senderid=PTLINK&channel=2&DCS=0&flashsms=0&number=91".$mobile_no."&text=".$msg1."&route=1&DLTtemplateid=1307161779377069155";

        $ret = file_get_contents($url);
        return $otp;
    }

    public function verify_otp(LoginRequest $request) {
        $mobile_no = $request->get('mobile');
        $get_otp = $request->get('otp');
        
        if($mobile_no != '' && $get_otp) {
            $count=CustomerMaster::where('mobile', $mobile_no)->where('otp', $get_otp)->count();
            if($count >= 1){
                $data=CustomerMaster::where('mobile', $mobile_no);
                $datas=array("otp"=>"Y");
                $data->update($datas);
            }
            echo $count;
        }
    }
    
    public function set_password(LoginRequest $request) {
        $mobile = $request->get('mobile');
        $password = $request->get('password');
        if( $mobile != '' && $password != '' ) {
            $datas = array("password"=>md5($password), "active"=>'1');
            $data = CustomerMaster::where('mobile', $mobile)->first();
            $data->update($datas);
        }
    }
}

