<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSearchCompany extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_master', function (Blueprint $table) {
            $table->string('mobile_show_stocks_from')->default('PTL,Pap Tech,Paper Hub')->nullable()->after('stock_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_master', function (Blueprint $table) {
            $table->dropColumn('mobile_show_stocks_from');
        });
    }
}
