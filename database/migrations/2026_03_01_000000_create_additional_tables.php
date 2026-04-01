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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->string('action_type');
            $table->text('action_description');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->dateTime('performed_at')->useCurrent();
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->string('commentable_type');
            $table->unsignedBigInteger('commentable_id');
            $table->foreignId('parent_comment_id')->nullable()->constrained('comments')->onDelete('cascade');
            $table->text('comment_text'); // Changed from comment to match model usage in some places, but model fillable says comment_text? Wait, let me check model again.
            // Model fillable says 'comment_text'. NCRService uses 'comment' key in create method?
            // NCRService: $ncr->comments()->create(['comment' => $comments ...])
            // If model fillable has comment_text, NCRService might be failing if it passes 'comment'.
            // I should check NCRService again later. For now, let's use 'comment_text' as per Model fillable.
            // Wait, if NCRService passes 'comment', I should probably fix NCRService or Model.
            // Let's stick to Model definition for migration: comment_text.
            $table->boolean('is_internal')->default(false);
            $table->foreignId('commented_by_user_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('commented_at')->useCurrent();
            $table->boolean('is_edited')->default(false);
            $table->dateTime('edited_at')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipient_user_id')->constrained('users')->onDelete('cascade');
            $table->string('notification_type');
            $table->string('title');
            $table->text('message');
            $table->string('related_entity_type')->nullable();
            $table->unsignedBigInteger('related_entity_id')->nullable();
            $table->string('action_url')->nullable();
            $table->string('priority')->default('Normal');
            $table->boolean('is_read')->default(false);
            $table->dateTime('read_at')->nullable();
            $table->boolean('is_email_sent')->default(false);
            $table->dateTime('email_sent_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('activity_logs');
    }
};
