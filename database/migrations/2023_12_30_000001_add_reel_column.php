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
    }
}
