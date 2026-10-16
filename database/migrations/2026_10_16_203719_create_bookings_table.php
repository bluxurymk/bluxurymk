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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->foreignId('stylist_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email');
            $table->text('note')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('status')->default('pending_deposit');
            $table->unsignedInteger('deposit_amount')->default(0);
            $table->dateTime('deposit_due_at')->nullable();
            $table->dateTime('deposit_paid_at')->nullable();
            $table->string('manage_token', 64)->unique();
            $table->string('google_event_id')->nullable();
            $table->dateTime('consent_at');
            $table->dateTime('received_sent_at')->nullable();
            $table->dateTime('deposit_reminder_sent_at')->nullable();
            $table->dateTime('reminder_sent_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->timestamps();

            $table->index(['stylist_id', 'starts_at']);
            $table->index(['status', 'deposit_due_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
