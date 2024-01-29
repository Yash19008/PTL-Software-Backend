<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReelColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_group', function (Blueprint $table) {
            $table->enum('is_reel', ['Yes', 'No'])->after('group_name')->default('No');
        });
        Schema::table('stock_columns', function (Blueprint $table) {
            $table->enum('is_reel', ['Yes', 'No'])->default('No');
        });
        Schema::table('order_master', function (Blueprint $table) {
            $table->enum('is_reel', ['Yes', 'No'])->default('No');
        });

        DB::statement("CREATE OR REPLACE VIEW order_master_view AS
                        select om.id, om.date, om.cust_id, cm.company_name, om.quality, om.size_inch_length, om.size_inch_width , om.gsm, om.qty, om.weight, om.delivery_at, om.challan_number,om.gwd, om.last_searched_id
                        , om.challan_bulk_action_date, om.challan_id, om.pkt_grs_weight, om.sheet, om.pkg_mode, om.pkt_grs, om.loc, om.bdls, om.size_cms_length, om.size_cms_width, om.company, om.is_reel,
                         if(om.status = 'P','Pending',if(om.status = 'B','Booked',if(om.status = 'NS','Not In Stock','Cancel'))) as status 
                        from order_master om
                        left join customer_master cm on om.cust_id=cm.id");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_group', function (Blueprint $table) {
            $table->dropColumn('is_reel');
        });
        Schema::table('stock_columns', function (Blueprint $table) {
            $table->dropColumn('is_reel');
        });
        Schema::table('order_master', function (Blueprint $table) {
            $table->dropColumn('is_reel');
        });
    }
}
