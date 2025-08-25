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
        Schema::create('users', function (Blueprint $table) {
    $table->string('user_identifier', 50)->primary();
    $table->string('password');
    $table->string('gayabelajar')->nullable();;


    $table->string('name',255);
    $table->enum('gender',['laki-laki','perempuan'])->nullable();
    $table->enum('role',['siswa','guru'])->default('siswa');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
