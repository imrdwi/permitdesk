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
        Schema::create('permit_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_temporary')->default(false);
            $table->string('practice_name');
            $table->text('practice_address');
            $table->string('practice_email')->nullable();
            $table->string('practice_phone')->nullable();
            $table->string('practice_fax')->nullable();
            $table->string('status')->index();
            $table->string('permit_number')->nullable()->unique();
            $table->text('rejection_reason')->nullable();
            $table->date('expires_on')->nullable()->index();
            $table->string('verification_code')->nullable()->unique();
            $table->timestamp('submitted_at');
            $table->timestamp('decided_at')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expiry_reminded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permit_applications');
    }
};
