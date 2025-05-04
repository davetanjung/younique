<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('analyzer_request_cloth', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clothing_id');
            $table->foreign('clothing_id')->references('id')->on('clothes')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('analyzer_request_id');
            $table->foreign('analyzer_request_id')->references('id')->on('analyzer_requests')->onDelete('cascade')->onUpdate('cascade');            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analyzer_request_cloth');
    }
};
