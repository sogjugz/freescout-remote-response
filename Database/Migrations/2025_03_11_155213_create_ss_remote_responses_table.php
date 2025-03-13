<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSsRemoteResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ss_remote_responses', function (Blueprint $table) {
            $table->integer('mailbox_id')->primary();
            $table->boolean('enabled');
            $table->string('url', 1024);
            $table->integer('timeout')->default(30);
            $table->string('method', 10)->default('POST');
            $table->json('headers', 1024)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ss_remote_responses');
    }
}   
 