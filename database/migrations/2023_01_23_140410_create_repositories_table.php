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
        Schema::create('repositories', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('repos_name');
            $table->integer('owner_id');
            $table->foreign('owner_id')->references('id')->on('gh_profiles')->cascadeOnDelete();
            $table->string('owner_name');
            $table->timestamp('created_date');
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
        Schema::dropIfExists('repositories');
        $table->dropForeign(['owner_id']);
        $table->dropColumn(['owner_id']);
    }
};
