<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOtherColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('order_master', function (Blueprint $table) {
            $table->string('challan_bulk_action_date')->after('challan_number')->nullable();
            $table->string('challan_id')->after('challan_number')->nullable();
            $table->string('pkt_grs_weight')->nullable();
            $table->string('sheet')->nullable();
            $table->string('pkg_mode')->nullable();
            $table->string('pkt_grs')->nullable();
            $table->string('loc')->nullable();
            $table->string('bdls')->nullable();
            $table->string('size_cms_length')->nullable();
            $table->string('size_cms_width')->nullable();
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
    }
}
