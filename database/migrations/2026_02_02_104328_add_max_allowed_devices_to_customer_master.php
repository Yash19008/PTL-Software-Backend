<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMaxAllowedDevicesToCustomerMaster extends Migration
{
    public function up()
    {
        // customer_master
        if (Schema::hasTable('customer_master')) {
            Schema::table('customer_master', function (Blueprint $table) {
                if (!Schema::hasColumn('customer_master', 'max_allowed_devices')) {
                    $table->unsignedTinyInteger('max_allowed_devices')
                          ->default(2)
                          ->after('mobile_show_stocks_from');
                }
            });
        }

        // search_history_master
        if (Schema::hasTable('search_history_master')) {
            Schema::table('search_history_master', function (Blueprint $table) {
                if (!Schema::hasColumn('search_history_master', 'platform')) {
                    $table->enum('platform', ['mobile', 'web'])
                          ->default('mobile')
                          ->after('product_group');
                }
            });
        }
    }

    public function down()
    {
        Schema::table('customer_master', function (Blueprint $table) {
            if (Schema::hasColumn('customer_master', 'max_allowed_devices')) {
                $table->dropColumn('max_allowed_devices');
            }
        });

        Schema::table('search_history_master', function (Blueprint $table) {
            if (Schema::hasColumn('search_history_master', 'platform')) {
                $table->dropColumn('platform');
            }
        });
    }
}
