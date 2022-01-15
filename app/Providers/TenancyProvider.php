<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use \Illuminate\Queue\Events\JobProcessing;
use App\Models\V1\Tenant\Tenant;

class TenancyProvider extends ServiceProvider{
    
    public function boot(){
        $this->configureQueue();
    }

    public function configureQueue(){
        $this->app["queue"]->createPayloadUsing(function() {
            return $this->app['tenant'] ? ['tenant' => $this->app["tenant"]->id] : [];
        });

        $this->app["events"]->listen(JobProcessing::class, function ($event) {
            if(isset($event->job->payload()['tenant'])){
                Tenant::find($event->job->payload()['tenant'])->configure()->use();
            }
        });
    }
}