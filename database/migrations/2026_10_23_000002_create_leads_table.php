<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->string('type', 16)->index();
            $table->string('status', 16)->default('new')->index();
            $table->string('name', 120);
            $table->string('phone', 30);
            $table->string('phone_digits', 20)->nullable()->index();
            $table->string('email')->nullable();
            $table->string('contact_pref', 10)->default('phone');
            // What the lead is about: a stock unit, or a line-up model key from config.
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->string('model_key', 40)->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            // Test drives and service bookings.
            $table->date('appointment_date')->nullable()->index();
            $table->string('appointment_time', 5)->nullable();
            // Type-specific answers (service type, mileage, trade-in vehicle, income band ...).
            $table->json('details')->nullable();
            $table->text('message')->nullable();
            // Finance pre-qualifications only.
            $table->string('finance_status', 20)->nullable()->index();
            $table->timestamp('consented_at')->nullable();
            $table->string('source_url', 300)->nullable();
            $table->timestamp('first_contacted_at')->nullable();
            $table->timestamp('status_changed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('lead_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // note | status | assign | finance | system
            $table->string('kind', 12)->default('note');
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('lead_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            // id | payslip | part | trade_in
            $table->string('kind', 12);
            // Always the private "local" disk; streamed to authorised staff only.
            $table->string('path');
            $table->string('original_name', 200);
            $table->string('mime', 100)->nullable();
            $table->unsignedInteger('size')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_files');
        Schema::dropIfExists('lead_notes');
        Schema::dropIfExists('leads');
    }
};
