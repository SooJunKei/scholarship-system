<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('academic_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->decimal('gpa', 3, 2);
            $table->string('semester');
            $table->string('academic_year');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('academic_records'); }
};
