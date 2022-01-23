<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGsmGroupNotification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('push_not_data', function (Blueprint $table) {
            $table->string('gsm')->after('size_in_inch')->nullable();
            $table->string('product_group')->after('size_in_inch')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('push_not_data', function (Blueprint $table) {
            $table->dropColumn('gsm');
            $table->dropColumn('product_group');
        });
    }
}
