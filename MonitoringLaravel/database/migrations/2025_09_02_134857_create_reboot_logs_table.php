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
        Schema::create('reboot_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->onDelete('cascade');
            $table->foreignId('incident_id')->constrained()->onDelete('cascade');
            $table->string('droplet_id');
            $table->enum('status', ['initiated', 'in_progress', 'completed', 'failed'])->default('initiated');
            $table->string('action_type')->default('reboot');
            $table->text('api_response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('initiated_at');
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reboot_logs');
    }
};
