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
            $user = JWTAuth::parseToken()->authenticate();
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
