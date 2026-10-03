<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('scholarships', function (Blueprint $table) {
            $table->id();
            $table->string('scholarship_name');
            $table->text('description');
            $table->string('sponsor');
            $table->decimal('amount', 10, 2);
            $table->date('application_deadline');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('scholarships'); }
};
