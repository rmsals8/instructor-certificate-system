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
        Schema::create('salary_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_statement_id')->constrained()->onDelete('cascade');
            $table->decimal('per_student_fee', 10, 2);
            $table->integer('student_count');
            $table->decimal('subsidy_amount', 10, 2)->default(0);
            $table->decimal('additional_payment', 10, 2)->default(0);
            $table->decimal('cancellation_refund', 10, 2)->default(0);
            $table->decimal('other_refund', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_details');
    }
};
