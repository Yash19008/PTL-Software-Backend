<?php

namespace App\Services;

use App\Models\V1\Operations\CustomerMaster;
use App\Models\UserDevice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeviceManager
{
    public function registerDevice(CustomerMaster $user, string $deviceType, string $token, array $deviceData = [])
    {
        try {
            $deviceId = $this->generateDeviceId($deviceData);
            
            $existingDevice = UserDevice::where('user_id', $user->id)
                ->where('device_id', $deviceId)
                ->first();

            if ($existingDevice) {
                $existingDevice->update([
                    'token' => $token,
                    'device_type' => $deviceType,
                    'active' => 1,
                    'last_active_at' => now(),
                    'ip_address' => request()->ip(),
                    'device_info' => $deviceData['device_info'] ?? null,
                    'onesignal_user_id' => $deviceData['oneSignalUserId'] ?? null,
                    'onesignal_token_id' => $deviceData['oneSignalTokenId'] ?? null,
                ]);
                
                return [
                    'success' => true,
                    'device' => $existingDevice,
                    'message' => 'Device updated successfully'
                ];
            }

            return [
                'success' => true,
                'device' => $this->createDevice($user, $deviceType, $token, $deviceId, $deviceData),
                'message' => 'Device registered successfully'
            ];

        } catch (\Exception $e) {
            Log::error('DeviceManager@registerDevice failed', [
                'user_id' => $user->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to register device'
            ];
        }
    }

    private function createDevice(CustomerMaster $user, string $deviceType, string $token, string $deviceId, array $deviceData)
    {
        return UserDevice::create([
            'user_id' => $user->id,
            'device_type' => $deviceType,
            'device_id' => $deviceId,
            'token' => $token,
            'active' => 1,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'device_info' => $deviceData['device_info'] ?? null,
            'onesignal_user_id' => $deviceData['oneSignalUserId'] ?? null,
            'onesignal_token_id' => $deviceData['oneSignalTokenId'] ?? null,
            'last_active_at' => now(),
        ]);
    }

    private function generateDeviceId(array $deviceData): string
    {
        if (isset($deviceData['unique_device_id'])) {
            return hash('sha256', $deviceData['unique_device_id']);
        }

        if (isset($deviceData['qr_token'])) {
            return hash('sha256', $deviceData['qr_token']);
        }

        $identifier = request()->userAgent() . request()->ip() . time();
        return hash('sha256', $identifier);
    }

    private function removeOldestDevice(CustomerMaster $user)
    {
        $oldestDevice = $user->devices()
            ->orderBy('last_active_at', 'asc')
            ->first();

        if ($oldestDevice) {
            DB::table('qr_sessions')
                ->where('login_token', $oldestDevice->token)
                ->update(['is_used' => false]);

            $deviceInfo = $oldestDevice->toArray();
            $oldestDevice->delete();
            
            return $deviceInfo;
        }

        return null;
    }

    public function logoutDevice($deviceId, $userId)
    {
        try {
            $device = UserDevice::where('device_id', $deviceId)
                ->where('user_id', $userId)
                ->first();

            if ($device) {
                DB::table('qr_sessions')
                    ->where('login_token', $device->token)
                    ->update(['is_used' => false]);

                $device->update(['active' => 0]);
                
                return [
                    'success' => true,
                    'message' => 'Device logged out successfully'
                ];
            }

            return [
                'success' => false,
                'message' => 'Device not found'
            ];

        } catch (\Exception $e) {
            Log::error('DeviceManager@logoutDevice failed', [
                'device_id' => $deviceId,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to logout device'
            ];
        }
    }

    public function logoutAllDevices(CustomerMaster $user)
    {
        try {
            $tokens = $user->devices()->pluck('token')->toArray();

            if (!empty($tokens)) {
                DB::table('qr_sessions')
                    ->whereIn('login_token', $tokens)
                    ->update(['is_used' => false]);
            }

            $user->devices()->delete();

            return [
                'success' => true,
                'message' => 'All devices logged out successfully'
            ];

        } catch (\Exception $e) {
            Log::error('DeviceManager@logoutAllDevices failed', [
                'user_id' => $user->id,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to logout all devices'
            ];
        }
    }

    public function isDeviceActive($userId, $token)
    {
        return UserDevice::where('user_id', $userId)
            ->where('token', $token)
            ->where('active', 1)
            ->exists();
    }

    public function updateActivity($userId, $token)
    {
        UserDevice::where('user_id', $userId)
            ->where('token', $token)
            ->where('active', 1)
            ->update(['last_active_at' => now()]);
    }
}
