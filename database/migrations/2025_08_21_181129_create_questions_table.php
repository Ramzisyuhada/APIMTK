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
   Schema::create('questions', function (Blueprint $table) {
    $table->string('question_id', 20)->primary();     // Q001, Q002, ...
    $table->string('assessment_id', 20);              // FK -> assessments
    $table->unsignedInteger('question_number')->nullable(); // integer, bisa null
    $table->text('question_text')->nullable();
    $table->timestamps();

    $table->foreign('assessment_id')
          ->references('assessment_id')->on('assessments')
          ->onDelete('cascade');
});


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
