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
        Schema::create('study_cycle_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_cycle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('difficulty')->default(3);
            $table->unsignedTinyInteger('importance')->default(3);
            $table->unsignedTinyInteger('progress')->default(0);
            $table->timestamp('last_studied_at')->nullable();
            $table->decimal('priority_score', 5, 2)->default(0);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->unique(['study_cycle_id', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_cycle_items');
    }
};
