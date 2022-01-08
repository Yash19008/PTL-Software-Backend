<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutstandingView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE OR REPLACE VIEW outstanding_view AS
                        select id,date,customer_name,mobile,voucher_type,sum(total_amount) as total_amount,part_paid,sum(balance) as balance 
                        from outstanding
                        group by customer_name");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW outstanding_view");
    }
}
