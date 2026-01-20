<?php
// app/Http/Controllers/Api/QrAuthController.php
namespace App\Http\Controllers\V1\Authentication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\QrSession;
use App\QrLoginSession;
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

        \App\QrSession::create([
            'qr_token' => $qrToken,
            'expires_at' => now()->addMinutes(5),
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

        // 1️⃣ Find QR
        $qrSession = \App\QrSession::where('qr_token', $request->qr_token)->first();

        if (!$qrSession) {
            return $this->failure('Invalid QR Code', null, 400);
        }

        // 2️⃣ Expiry check
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

        // 🔥 5️⃣ FINAL CRITICAL CHECK — USER ALREADY LOGGED IN
        $alreadyLoggedIn = \App\QrSession::where('user_id', $user->id)
            ->where('is_used', true)
            ->where('expires_at', '>', now())
            ->exists();

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

        // 7️⃣ Mark QR as used
        $qrSession->update([
            'is_used' => true,
            'user_id' => $user->id,
            'login_token' => $token
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

    $qr = \App\QrSession::where('qr_token', $request->qr_token)->first();

    if (!$qr) {
        return $this->failure('Invalid QR', null, 404);
    }

    if (now()->gt($qr->expires_at)) {
        return $this->failure('QR expired', null, 410);
    }

    // 🔑 LOGIN SUCCESS
    if ($qr->is_used && $qr->login_token) {
        return $this->success('Logged in', [
            'logged_in' => true,
            'token'     => $qr->login_token
        ], 200);
    }

    return $this->success('Waiting for scan', [
        'logged_in' => false
    ], 200);
}



}
