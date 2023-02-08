<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pullrequests', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('repositories_id');
            $table->foreign('repositories_id')->references('id')->on('repositories')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('body')->nullable();
            $table->boolean('close_flag');
            $table->integer('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamp('open_date');
            $table->timestamp('close_date')->nullable();
            $table->timestamp('merge_date')->nullable();
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
        Schema::dropIfExists('pullrequests');

        $table->dropForeign(['repositories_id']);
        $table->dropColumn(['repositories_id']);
        $table->dropForeign(['user_id']);
        $table->dropColumn(['user_id']);
    }
};
