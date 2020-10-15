<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferralsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('referral_source_id')->index();
            $table->foreign('referral_source_id')->references('id')->on('users');

            $table->unsignedBigInteger('user_id')->unique();
            $table->foreign('user_id')->references('id')->on('users');

            $table->foreignId("bonus_id")
                ->nullable()
                ->constrained();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('referrals');
    }
}
