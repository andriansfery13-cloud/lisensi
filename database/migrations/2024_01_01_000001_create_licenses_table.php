<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('serial_number')->unique()->index();
            $table->string('product_name');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->enum('type', ['perpetual', 'yearly', 'monthly'])->default('perpetual');
            $table->enum('status', ['active', 'suspended', 'revoked', 'expired'])->default('active');
            $table->integer('max_domains')->default(1);
            $table->json('activated_domains')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->json('metadata')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
            // NO soft deletes - licenses cannot be deleted
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
