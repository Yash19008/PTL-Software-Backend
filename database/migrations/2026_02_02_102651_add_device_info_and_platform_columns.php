<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeviceInfoAndPlatformColumns extends Migration
{
    public function up()
    {
        // customer_master
        if (Schema::hasTable('customer_master')) {
            Schema::table('customer_master', function (Blueprint $table) {
                if (!Schema::hasColumn('customer_master', 'device_info')) {
                    $table->json('device_info')->nullable()->after('mobile_show_stocks_from');
                }
            });
        }

        // order_master
        if (Schema::hasTable('order_master')) {
            Schema::table('order_master', function (Blueprint $table) {
                if (!Schema::hasColumn('order_master', 'platform')) {
                    $table->enum('platform', ['mobile', 'web'])
                          ->default('mobile')
                          ->after('is_reel');
                }
            });
        }
    }

    public function down()
    {
        Schema::table('customer_master', function (Blueprint $table) {
            if (Schema::hasColumn('customer_master', 'device_info')) {
                $table->dropColumn('device_info');
            }
        });

        Schema::table('order_master', function (Blueprint $table) {
            if (Schema::hasColumn('order_master', 'platform')) {
                $table->dropColumn('platform');
            }
        });
    }
}
