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
         Schema::create('submissions', function (Blueprint $table) {
            $table->string('submission_id', 20)->primary();   // S001, dst (string)
            $table->string('user_identifier', 50);            // FK -> users.user_identifier
            $table->string('assessment_id', 20);              // FK -> assessments.assessment_id
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->foreign('user_identifier')
                  ->references('user_identifier')->on('users')->onDelete('cascade');
            $table->foreign('assessment_id')
                  ->references('assessment_id')->on('assessments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
