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
        Schema::create('workshop_assistance_requests', function (Blueprint $table) {
            $table->id();
            $table->float('price');
            $table->unsignedBigInteger('workshop_id');
            $table->unsignedBigInteger('assistance_request_id');
            $table->foreign('workshop_id')->references('id')->on('workshops')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('assistance_request_id')->references('id')->on('assistance_requests')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshop_assistance_requests');
    }
};
