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
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['detected', 'verified', 'notification_sent', 'resolved'])->default('detected');
            $table->text('error_details')->nullable();
            $table->integer('response_time')->nullable();
            $table->integer('status_code')->nullable();
            $table->timestamp('detected_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('notification_sent_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->json('verification_attempts')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
