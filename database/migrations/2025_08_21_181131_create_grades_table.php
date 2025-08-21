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
    Schema::create('grades', function (Blueprint $table) {
            $table->string('grade_id', 20)->primary();      // G_001, G_002
            $table->string('submission_id', 20);            // FK -> submissions.submission_id
            $table->string('user_identifier', 50);          // dari sheet: nomor_identitas
            $table->decimal('score', 6, 2)->default(0);
            $table->timestamps();

            $table->foreign('submission_id')
                  ->references('submission_id')->on('submissions')
                  ->onDelete('cascade');

            $table->foreign('user_identifier')
                  ->references('user_identifier')->on('users')
                  ->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
