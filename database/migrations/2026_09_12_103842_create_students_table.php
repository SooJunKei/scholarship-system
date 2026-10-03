<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_number')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->enum('gender', ['Male', 'Female']);
            $table->date('date_of_birth');
            $table->string('email');
            $table->string('phone');
            $table->text('address');
            $table->string('course');
            $table->integer('year_level');
            $table->string('department');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('students'); }
};
