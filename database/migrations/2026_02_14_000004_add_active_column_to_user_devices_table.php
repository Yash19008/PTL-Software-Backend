<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActiveColumnToUserDevicesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('user_devices') && !Schema::hasColumn('user_devices', 'active')) {
            Schema::table('user_devices', function (Blueprint $table) {
                $table->tinyInteger('active')->default(1)->after('token');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('user_devices', 'active')) {
            Schema::table('user_devices', function (Blueprint $table) {
                $table->dropColumn('active');
            });
        }
    }
}
