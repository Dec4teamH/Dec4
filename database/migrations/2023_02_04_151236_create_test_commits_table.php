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
        Schema::create('test_commits', function (Blueprint $table) {
            $table->id();
            $table->string("commit_id");
            $table->string("name");
            $table->string("message");
            $table->dateTime("create_at");
            $table->integer("repository_id");
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
        Schema::dropIfExists('test_commits');
    }
};
