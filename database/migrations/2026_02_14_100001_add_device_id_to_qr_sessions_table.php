<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeviceIdToQrSessionsTable extends Migration
{
    public function up()
    {
        Schema::table('qr_sessions', function (Blueprint $table) {
            $table->string('device_id')->nullable()->after('login_token');
            $table->enum('device_type', ['web', 'android', 'ios'])->default('web')->after('device_id');
        });
    }

    public function down()
    {
        Schema::table('qr_sessions', function (Blueprint $table) {
            $table->dropColumn(['device_id', 'device_type']);
        });
    }
}
