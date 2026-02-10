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

        // 3️⃣ Already used QR
        if ($qrSession->is_used) {
            return $this->failure('QR Code Already Used', null, 409);
        }

        // 4️⃣ Fetch user
        $user = UserMaster::find($request->user_id);
        if (!$user) {
            return $this->failure('User not found', null, 404);
        }

        $alreadyLoggedIn = DB::table('qr_sessions')
            ->where('user_id', $user->id)
            ->where('is_used', true)
            ->where('expires_at', '>', now())
            ->exists();
            // add now() to the query
        \Log::info('alreadyLoggedIn: ' . json_encode($alreadyLoggedIn) . ' now(): ' . now());

        if ($alreadyLoggedIn) {
            return $this->failure(
                'User is already logged in on another session',
                null,
                409
            );
        }

        // 6️⃣ Create JWT (password-less login)
        $payload = [
            'id' => (int) $user->id,
            'name' => $user->employee_name
        ];

        try {
            $token = $JWTAuth->fromUser($user, $payload);
        } catch (\Exception $e) {
            return $this->failure('Unable to login user', null, 500);
        }

        DB::table('qr_sessions')->where('id', $qrSession->id)->update([
            'is_used' => true,
            'user_id' => $user->id,
            'login_token' => $token,
            'updated_at' => now(),
        ]);

        // 8️⃣ Success response
        return $this->success('Login Successful via QR', [
            'token' => $token,
            'userDetails' => $user
        ], 200);
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
        // 🔑 LOGIN SUCCESS
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

}

