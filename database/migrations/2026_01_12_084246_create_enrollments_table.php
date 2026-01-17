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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('course_id');
            $table->string('stripe_session_id')->nullable();
            $table->string('payment_status')->default('pending');

            // $table->unique(['user_id', 'course_id']); // 90% race condition will be automically handle 
            // $table->string('stripe_session_id')->index(); // for read operation first
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
