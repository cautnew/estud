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
        Schema::create('study_plan_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_plan_subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('order')->default(0);
            $table->string('status')->default('pending');
            $table->timestamp('studied_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['study_plan_subject_id', 'topic_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_plan_topics');
    }
};
