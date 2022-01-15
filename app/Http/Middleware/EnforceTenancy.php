<?php

namespace App\Http\Middleware;
use App\Models\V1\Tenant\Tenant;
use Closure;

class EnforceTenancy
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
        //Get tenant_id from header
        $tenant_id = $request->header('X-TENANT-SLUG');

        if(!$tenant_id){
            //Try to get it from route params
            $tenant_id = $request->route('__tenant__');
        }
        $tenant = Tenant::getCompany($tenant_id);
        if($tenant != null && $tenant->active){
            $tenant->configure()->use();
        }else{
            return response(['message' => 'Bad Request','success' => false], 400);
        }
        return $next($request);
    }
}
