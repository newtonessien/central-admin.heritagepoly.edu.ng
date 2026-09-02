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
       Schema::create('admission_audits', function (Blueprint $table) {
    $table->id();

    $table->unsignedBigInteger('candidate_id');
    $table->string('regno', 255)->nullable();

    // Academic context at the time of the action
    $table->unsignedBigInteger('acad_session_id')->nullable();
    $table->unsignedBigInteger('program_type_id')->nullable();
    $table->unsignedBigInteger('application_type_id')->nullable();
    $table->unsignedBigInteger('faculty_id')->nullable();
    $table->unsignedBigInteger('department_id')->nullable();
    $table->unsignedBigInteger('program_id')->nullable();

    // Admission action
    $table->string('action', 30);
    $table->string('decision', 30)->nullable();
    $table->text('reason')->nullable();

    // Central Admin officer
    $table->unsignedBigInteger('performed_by')->nullable();

    $table->timestamp('performed_at')->nullable();

    // Additional information from Student Portal/API
    $table->json('metadata')->nullable();

    $table->timestamps();

    $table->index(['candidate_id']);
    $table->index(['regno']);
    $table->index(['acad_session_id']);
    $table->index(['action']);
    $table->index(['performed_by']);
    $table->index(['performed_at']);
});


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admission_audits');
    }
};
