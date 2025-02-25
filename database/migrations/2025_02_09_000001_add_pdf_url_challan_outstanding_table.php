<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPdfUrlChallanOutstandingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('challan_list', function (Blueprint $table) {
            $table->text('pdf_url')->after('status')->nullable();
        });
        Schema::table('outstanding', function (Blueprint $table) {
            $table->text('pdf_url')->after('balance')->nullable();
        });
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
