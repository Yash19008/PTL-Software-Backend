<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDevicesTable extends Migration
{
    public function up()
    {
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('device_type', ['web', 'android', 'ios']);
            $table->string('device_id')->unique();
            $table->text('device_info')->nullable();
            $table->text('token')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('onesignal_user_id')->nullable();
            $table->string('onesignal_token_id')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('customer_master')->onDelete('cascade');
            $table->index(['user_id', 'device_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_devices');
    }
}
