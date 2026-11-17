<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();

            // What the patient asked for.
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('phone', 30);
            $table->string('email');
            $table->string('patient_type', 20);
            $table->date('preferred_date');
            $table->string('preferred_time', 20);
            $table->string('note', 500)->nullable();

            // PDPA: when and to which version of the privacy notice the patient agreed.
            $table->timestamp('consented_at');
            $table->string('privacy_notice_version', 20);

            // How the clinic handled it.
            $table->string('status', 30)->default('pending')->index();
            $table->foreignId('dentist_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('appointment_at')->nullable();
            $table->dateTime('alternative_at')->nullable();
            $table->string('message_to_patient', 1000)->nullable();
            $table->text('staff_notes')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();

            $table->timestamps();
            $table->index('preferred_date');
            $table->index('appointment_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_requests');
    }
};
