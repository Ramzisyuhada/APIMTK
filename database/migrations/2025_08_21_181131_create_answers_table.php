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
     Schema::create('answers', function (Blueprint $table) {
            $table->string('answer_id', 20)->primary();   // AN001, dst
            $table->string('submission_id', 20);          // FK -> submissions
            $table->string('question_id', 20);            // FK -> questions
            $table->text('answer_text')->nullable();
            $table->timestamps();

            $table->foreign('submission_id')
                  ->references('submission_id')->on('submissions')->onDelete('cascade');

            $table->foreign('question_id')
                  ->references('question_id')->on('questions')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
