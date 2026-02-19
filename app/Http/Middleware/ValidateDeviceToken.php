<?php

namespace App\Http\Middleware;

use App\Services\DeviceManager;
use Closure;
use Illuminate\Http\Request;

class ValidateDeviceToken
{
    protected $deviceManager;

    public function __construct(DeviceManager $deviceManager)
    {
        $this->deviceManager = $deviceManager;
    }

    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('Authorization') ?? $request->get('token');
        $userId = $request->get('user_id') ?? $request->user()->id ?? null;

        if ($token && $userId) {
            $token = str_replace('Bearer ', '', $token);

            if (!$this->deviceManager->isDeviceActive($userId, $token)) {
                return response()->json([
                    'message' => 'Your session has been expired or logged out from another device. Please login again.',
                    'status' => 'error',
                    'code' => 'DEVICE_LOGGED_OUT'
                ], 401);
            }

            $this->deviceManager->updateActivity($userId, $token);
        }

        return $next($request);
    }
}
