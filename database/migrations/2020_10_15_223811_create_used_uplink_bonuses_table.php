<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsedUplinkBonusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('used_uplink_bonuses', function (Blueprint $table) {
            $table->id();

            $table->foreignId("uplink_id")->constrained("users");
            $table->foreignId("user_id")->constrained();

            $table->unique(["uplink_id", "user_id"]);

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
        Schema::dropIfExists('used_uplink_bonuses');
    }
}
