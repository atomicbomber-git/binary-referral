<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferralPathsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referral_paths', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('ancestor_id')->index();
            $table->foreign('ancestor_id')->references('id')->on('users')->cascadeOnDelete();

            $table->unsignedBigInteger('descendant_id')->index();
            $table->foreign('descendant_id')->references('id')->on('users')->cascadeOnDelete();

            $table->integer("tree_depth");

            $table->unique(['ancestor_id', 'descendant_id']);
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
        Schema::dropIfExists('referral_paths');
    }
}
