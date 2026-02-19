<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JwtMiddleware extends BaseMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $guardType = $payload->get('guard', 'api');
            
            $user = null;
            if ($guardType === 'customer') {
                $user = \App\Models\V1\Operations\CustomerMaster::find($payload->get('sub'));
            } else {
                $user = \App\Models\V1\Operations\UserMaster::find($payload->get('sub'));
                if (!$user) {
                    $user = \App\Models\V1\Operations\CustomerMaster::find($payload->get('sub'));
                    $guardType = 'customer';
                }
            }
            
            if (!$user) {
                $data = [
                    'responseCode' => [
                        'code' => 'EC201',
                        'message' => 'User not found'
                    ],
                    'success' => false,
                    'err' => 'User associated with this token does not exist',
                    'errSeverity' => 1
                ];
                return response()->json($data, 401);
            }
            
            \Auth::setUser($user);
        } catch (Exception $e) {
            \Log::channel('single')->warning('JWT auth failed', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            $data = [
                'responseCode' => [
                    'code' => 'EC201',
                    'message' => ''
                ],
                'success' => false,
                'err' => 'Something went wrong',
                'errSeverity' => 1
            ];

            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                $data['responseCode']['message'] = 'Token is Invalid';
                return response()->json($data);
            }
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                $data['responseCode']['message'] = 'Token is Expired';
                return response()->json($data);
            }
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenBlacklistedException) {
                $data['responseCode']['message'] = 'Token has been invalidated';
                return response()->json($data);
            }
            $data['responseCode']['message'] = 'Authorization Token not found';
            return response()->json($data);
        }
        return $next($request);
    }
}
