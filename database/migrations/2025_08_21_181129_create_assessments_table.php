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
        Schema::create('assessments', function (Blueprint $table) {
            $table->string('assessment_id', 20)->primary();   // PK (A_001, dst)
            $table->string('assessment_type', 50);            // Exercise / Post_Test
            $table->string('title', 255);                     // sesuai diagram: "title"
            $table->string('file_url_soal', 500)->nullable(); // link GDrive
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
