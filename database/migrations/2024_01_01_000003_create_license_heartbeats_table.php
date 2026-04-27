<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_heartbeats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained('licenses')->cascadeOnUpdate();
            $table->string('domain');
            $table->string('ip_address');
            $table->string('server_signature')->nullable();
            $table->string('response_status');
            $table->timestamp('checked_at');
            $table->timestamps();
            
            $table->index(['license_id', 'checked_at']);
            $table->index('domain');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_heartbeats');
    }
};
