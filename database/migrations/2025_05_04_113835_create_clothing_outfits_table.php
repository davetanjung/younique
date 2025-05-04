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
        Schema::create('clothing_outfits', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('outfit_id');
            $table->unsignedBigInteger('clothing_id');            
            
            // Foreign key relationships
            $table->foreign('clothing_id')->references('id')->on('clothes')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('outfit_id')->references('id')->on('outfits')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clothing_outfits');
    }
};
