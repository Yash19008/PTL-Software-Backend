<?php

// database/migrations/xxxx_create_qr_sessions_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQrSessionsTable extends Migration
{
    public function up()
    {
        Schema::create('qr_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('qr_token')->unique();
            $table->boolean('is_used')->default(false);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('login_token')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('qr_sessions');
    }
}
