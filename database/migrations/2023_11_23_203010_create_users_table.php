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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('password');
            $table->string('type');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('workshop_id')->nullable();
            $table->unsignedBigInteger('technician_id')->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('workshop_id')->references('id')->on('workshops')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('technician_id')->references('id')->on('technicians')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
