<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('title');
            $table->text('body');
            $table->string('file')->nullable();
            $table->tinyInteger('status')->default(0);

            $table->timestamps();
        });

        Schema::create('tickets_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('role_id')->constrained('auth_roles')->restrictOnDelete();
            $table->unsignedInteger('round')->default(1);
            $table->tinyInteger('status')->default(0);
            $table->text('review');
            $table->timestamps();

            $table->unique(['ticket_id']);
            $table->unique(['ticket_id', 'admin_id', 'role_id', 'round']);
        });


        Schema::create('tickets_api_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->unsignedInteger('attempt')->default(1);
            $table->string('status', 32)->index();
            $table->string('request_id')->nullable();
            $table->string('idempotency_key')->unique();
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->json('response_body')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['ticket_id', 'status',]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets_api_requests');
        Schema::dropIfExists('tickets_approvals');
        Schema::dropIfExists('tickets');
    }
};
