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
        Schema::create('applicant_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('full_name');
            $table->text('address');
            $table->string('birth_place');
            $table->date('birth_date');
            $table->string('gender');
            $table->unsignedSmallInteger('graduation_year');
            $table->string('license_number')->nullable();
            $table->date('license_expires_on')->nullable();
            $table->string('university')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicant_profiles');
    }
};
