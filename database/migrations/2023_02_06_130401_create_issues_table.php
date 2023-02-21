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
        Schema::create('issues', function (Blueprint $table) {
            $table->integer('id')->primary('id');
            $table->integer('repositories_id');
            $table->foreign('repositories_id')->references('id')->on('repositories')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('body')->nullable();
            $table->integer('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('gh_profiles')->cascadeOnDelete();
            $table->integer('assign_id')->nullable();
            $table->foreign('assign_id')->references('id')->on('gh_profiles')->cascadeOnDelete();
            $table->boolean('close_flag');
            $table->timestamp('start_at')->nullable();
            $table->timestamp('open_date');
            $table->timestamp('close_date')->nullable();
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
        Schema::dropIfExists('issues');

        $table->dropForeign(['repositories_id']);
        $table->dropColumn(['repositories_id']);
        $table->dropForeign(['user_id']);
        $table->dropColumn(['user_id']);
        $table->dropForeign(['assign_id']);
        $table->dropColumn(['assign_id']);
    }
};
