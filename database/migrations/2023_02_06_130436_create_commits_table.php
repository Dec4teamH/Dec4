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
        Schema::create('commits', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('repositories_id');
            $table->foreign('repositories_id')->references('id')->on('repositories')->cascadeOnDelete();
            $table->string('sha');
            $table->bigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('gh_profiles')->cascadeOnDelete();
            $table->string('message')->nullable();
            $table->timestamp('commit_date');
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
        Schema::dropIfExists('commits');

        $table->dropForeign(['repositories_id']);
        $table->dropColumn(['repositories_id']);
        $table->dropForeign(['user_id']);
        $table->dropColumn(['user_id']);
    }
};
