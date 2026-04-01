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
        Schema::create('capa_progress_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('capa_id')->constrained('capas')->onDelete('cascade');
            $table->integer('progress_percentage')->default(0);
            $table->text('milestone_description')->nullable();
            $table->text('activities_completed')->nullable();
            $table->text('challenges_encountered')->nullable();
            $table->text('next_steps')->nullable();
            $table->foreignId('logged_by_user_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('logged_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capa_progress_logs');
    }
};
