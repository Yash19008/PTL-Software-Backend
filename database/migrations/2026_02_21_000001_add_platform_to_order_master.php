<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPlatformToOrderMaster extends Migration
{
    public function up()
    {
        if (Schema::hasTable('order_master') && !Schema::hasColumn('order_master', 'platform')) {
            Schema::table('order_master', function (Blueprint $table) {
                $table->string('platform', 20)->default('mobile')->after('is_reel');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('order_master') && Schema::hasColumn('order_master', 'platform')) {
            Schema::table('order_master', function (Blueprint $table) {
                $table->dropColumn('platform');
            });
        }
    }
}
