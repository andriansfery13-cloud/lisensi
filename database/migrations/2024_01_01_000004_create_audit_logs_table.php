<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->nullable()->constrained('licenses')->nullOnDelete();
            $table->string('action'); // created, activated, suspended, revoked, transferred, heartbeat_fail
            $table->string('actor'); // admin, system, api
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at');
            
            $table->index(['license_id', 'created_at']);
            $table->index('action');
            // IMMUTABLE: No update or delete on this table
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
