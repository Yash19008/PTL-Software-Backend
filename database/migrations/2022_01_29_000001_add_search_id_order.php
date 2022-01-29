<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSearchIdOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_master', function (Blueprint $table) {
            $table->integer('last_searched_id')->after('challan_number')->nullable();
        });
        
        DB::statement("CREATE OR REPLACE VIEW order_master_view AS
                        select om.id, om.date, om.cust_id, cm.company_name, om.quality, om.size_inch_length, om.size_inch_width , om.gsm, om.qty, om.weight, om.delivery_at, om.challan_number, om.last_searched_id
                        ,if(om.status = 'P','Pending',if(om.status = 'B','Booked',if(om.status = 'NS','Not In Stock','Cancel'))) as status 
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
        Schema::table('order_master', function (Blueprint $table) {
            $table->dropColumn('last_searched_id');
        });
    }
}
