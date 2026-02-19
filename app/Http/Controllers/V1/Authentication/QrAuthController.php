<?php
// app/Http/Controllers/Api/QrAuthController.php
namespace App\Http\Controllers\V1\Authentication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTAuth;

use App\Models\V1\Operations\UserMaster;
use App\Models\V1\Operations\CustomerMaster;
use App\Models\V1\Operations\AuditTrail;

class QrAuthController extends Controller
{
    public function generateQr()
    {
        $qrToken = \Illuminate\Support\Str::uuid()->toString();

        $data = DB::table('qr_sessions')->insert([
            'qr_token' => $qrToken,
            'expires_at' => now()->addSeconds(300),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $qrSvg = (string) \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
            ->size(300)
            ->generate(json_encode([
                'qr_token' => $qrToken
            ]));

        return response()->json([
            'success' => true,
            'data' => [
                'qr_token' => $qrToken,
                'qr_code_svg' => $qrSvg,
                'expires_in' => 300
            ]
        ]);
    }

    public function scanQrLogin(Request $request, \Tymon\JWTAuth\JWTAuth $JWTAuth)
    {
        $request->validate([
            'qr_token' => 'required|string',
            'user_id' => 'required|integer'
        ]);

        $qrSession = DB::table('qr_sessions')->where('qr_token', $request->qr_token)->first();

        if (!$qrSession) {
            return $this->failure('Invalid QR Code', null, 400);
        }

        if (now()->gt($qrSession->expires_at)) {
            return $this->failure('QR Code Expired', null, 410);
        }

        $user = CustomerMaster::find($request->user_id);
        if (!$user) {
            return $this->failure('User not found', null, 404);
        }

        if ($qrSession->is_used) {
            if ((int) $qrSession->user_id === (int) $request->user_id && !empty($qrSession->login_token)) {
                $deviceManager = app(\App\Services\DeviceManager::class);
                $deviceManager->updateActivity($user->id, $qrSession->login_token);
                $user->is_admin = false;
                return $this->success('Login Successful via QR', [
                    'token' => $qrSession->login_token,
                    'userDetails' => $user,
                    'already_logged_in' => true
                ], 200);
            }
            return $this->failure('QR Code Already Used', null, 409);
        }

        $deviceId = hash('sha256', $request->qr_token);
        $existingDevice = \App\Models\UserDevice::where('user_id', $user->id)->where('device_id', $deviceId)->exists();

        if (!$existingDevice) {
            $activeDevicesCount = $user->devices()->count();
            $maxDevices = $user->getMaxAllowedDevices();
            // echo $maxDevices; die; 
            if (!$user->hasUnlimitedDevices() && $activeDevicesCount >= $maxDevices) {
                return $this->failure(
                    "Device limit exceeded! Maximum {$maxDevices} device(s) allowed. Please logout from another device first.",
                    [
                        'max_devices' => $maxDevices,
                        'active_devices' => $activeDevicesCount,
                        'code' => 'DEVICE_LIMIT_EXCEEDED'
                    ],
                    403
                );
            }
        }

        $customClaims = [
            'id' => (int) $user->id,
            'name' => $user->employee_name,
            'guard' => 'customer'
        ];

        try {
            $token = $JWTAuth->customClaims($customClaims)->fromUser($user);
        } catch (\Exception $e) {
            \Log::error('QR Login Token Generation Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->failure('Unable to login user', null, 500);
        }

        $deviceInfo = $request->get('device_info');
        if (is_string($deviceInfo)) {
            $deviceInfo = json_decode($deviceInfo, true) ?: [];
        }
        $deviceInfo = is_array($deviceInfo) ? $deviceInfo : [];

        $deviceManager = app(\App\Services\DeviceManager::class);
        $deviceResult = $deviceManager->registerDevice(
            $user,
            'web',
            $token,
            array_merge([
                'qr_token' => $request->qr_token,
                'unique_device_id' => $deviceId
            ], ['device_info' => $deviceInfo])
        );

        if (!$deviceResult['success']) {
            $message = $deviceResult['message'] ?? 'Failed to register device';
            $code = $deviceResult['code'] ?? null;
            return $this->failure($message, ['code' => $code], 403);
        }

        DB::table('qr_sessions')->where('id', $qrSession->id)->update([
            'is_used' => true,
            'user_id' => $user->id,
            'login_token' => $token,
            'device_id' => $deviceId,
            'device_type' => 'web',
            'updated_at' => now(),
        ]);

        $user->is_admin = false;
        $response = [
            'token' => $token,
            'userDetails' => $user,
            'device_info' => $deviceResult['device'],
            'device_limit_info' => [
                'max_devices' => $user->getMaxAllowedDevices(),
                'unlimited' => $user->hasUnlimitedDevices(),
                'active_devices' => $user->devices()->count()
            ]
        ];

        return $this->success('Login Successful via QR', $response, 200);
    }

    public function checkQrStatus(Request $request)
    {
        $request->validate([
            'qr_token' => 'required|string'
        ]);
    
        $qr = DB::table('qr_sessions')->where('qr_token', $request->qr_token)->first();
    
        if (!$qr) {
            return $this->failure('Invalid QR', null, 404);
        }
    
        if (now()->gt($qr->expires_at)) {
            return $this->failure('QR expired', null, 410);
        }
    
        \Log::info('QR: ' . json_encode($qr));
        if ($qr->is_used && $qr->login_token) {
            $user = CustomerMaster::find($qr->user_id);
            
            return $this->success('Logged in', [
                'logged_in' => true,
                'token'     => $qr->login_token,
                'customer_data' => [
                    'id' => $user->id,
                    'mobile' => $user->mobile,
                    'employee_name' => $user->employee_name
                ]
            ], 200);
        }
    
        return $this->success('Waiting for scan', [
            'logged_in' => false
        ], 200);
    }

    public function webLogout(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'user_id' => 'required|integer'
        ]);

        $user = CustomerMaster::find($request->user_id);
        
        if (!$user) {
            return $this->failure('User not found', null, 404);
        }

        $device = \App\Models\UserDevice::where('user_id', $user->id)
            ->where('token', $request->token)
            ->first();

        if ($device) {
            $deviceManager = app(\App\Services\DeviceManager::class);
            $result = $deviceManager->logoutDevice($device->device_id, $user->id);
            
            if ($result['success']) {
                return $this->success('Logged out successfully', null, 200);
            }
        }

        return $this->failure('Device not found', null, 404);
    }

    public function logoutAllDevices(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer'
        ]);

        $user = CustomerMaster::find($request->user_id);
        
        if (!$user) {
            return $this->failure('User not found', null, 404);
        }

        $deviceManager = app(\App\Services\DeviceManager::class);
        $result = $deviceManager->logoutAllDevices($user);

        if ($result['success']) {
            return $this->success($result['message'], null, 200);
        }

        return $this->failure($result['message'], null, 500);
    }

}

