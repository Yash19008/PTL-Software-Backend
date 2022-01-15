<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Auth\Access\AuthorizationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if($request->is('api/*')) {
            if($exception instanceof AuthenticationException){
                return response()->json([
                    'message' => __($exception->getMessage()),
                    'success' => false
                ], 401);
            }
            if($exception instanceof NotFoundHttpException){
                return response()->json([
                    'message' => __("Not found"),
                    'success' => false
                ], 404);
            }
            if($exception instanceof AuthorizationException){
                return response()->json([
                    'message' => __($exception->getMessage()),
                    'success' => false
                ], 403);
            }
        }
        return parent::render($request, $exception);
    }
}
