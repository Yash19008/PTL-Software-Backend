<?php

namespace App\Http\Controllers\V1\Authentication;

use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTAuth;

use App\Models\V1\Operations\UserMaster;

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

        $user_payload['token'] = $token;
        $user_payload['userDetails'] = $user;
        return $this->success('Login Successully !!', $user_payload, 200);
    }
}
