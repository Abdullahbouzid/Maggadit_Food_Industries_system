<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('surveys')) {
            Schema::create('surveys', function (Blueprint $table) {
                $table->id();
                $table->string('title', 255);
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('estimated_minutes')->default(5);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index('is_active');
                $table->index('created_by');
            });
        }

        if (!Schema::hasTable('survey_questions')) {
            Schema::create('survey_questions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('survey_id')->constrained('surveys')->cascadeOnDelete();
                $table->text('question_text');
                $table->string('question_type', 30)->default('text');
                $table->json('options')->nullable();
                $table->boolean('is_required')->default(false);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->index('survey_id');
                $table->index('sort_order');
            });
        }

        if (!Schema::hasTable('survey_responses')) {
            Schema::create('survey_responses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('survey_id')->constrained('surveys')->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamps();

                $table->unique(['survey_id', 'customer_id']);
                $table->index('submitted_at');
            });
        }

        if (!Schema::hasTable('survey_answers')) {
            Schema::create('survey_answers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('survey_response_id')->constrained('survey_responses')->cascadeOnDelete();
                $table->foreignId('survey_question_id')->constrained('survey_questions')->cascadeOnDelete();
                $table->text('answer_text')->nullable();
                $table->timestamps();

                $table->index('survey_response_id');
                $table->index('survey_question_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_answers');
        Schema::dropIfExists('survey_responses');
        Schema::dropIfExists('survey_questions');
        Schema::dropIfExists('surveys');
    }
};
