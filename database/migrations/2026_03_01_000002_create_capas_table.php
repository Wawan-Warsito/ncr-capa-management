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
        Schema::create('capas', function (Blueprint $table) {
            $table->id();
            $table->string('capa_number')->unique();
            $table->foreignId('ncr_id')->constrained('ncrs')->onDelete('cascade');
            
            // RCA (Root Cause Analysis)
            $table->string('rca_method')->nullable();
            $table->text('root_cause_summary')->nullable();
            
            // 5 Whys
            $table->string('why_1')->nullable();
            $table->string('why_2')->nullable();
            $table->string('why_3')->nullable();
            $table->string('why_4')->nullable();
            $table->string('why_5')->nullable();
            
            // Fishbone
            $table->string('fishbone_people')->nullable();
            $table->string('fishbone_process')->nullable();
            $table->string('fishbone_material')->nullable();
            $table->string('fishbone_equipment')->nullable();
            $table->string('fishbone_environment')->nullable();
            $table->string('fishbone_measurement')->nullable();
            
            // Actions
            $table->text('corrective_action_plan')->nullable();
            $table->text('preventive_action_plan')->nullable();
            $table->text('expected_outcome')->nullable();
            
            // Assignment
            $table->foreignId('assigned_pic_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('assigned_at')->nullable();
            
            // Progress
            $table->date('target_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            $table->integer('progress_percentage')->default(0);
            $table->string('current_status')->default('Draft');
            
            // Verification
            $table->boolean('effectiveness_verified')->default(false);
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->string('verification_method')->nullable();
            $table->text('verification_results')->nullable();
            
            // Monitoring
            $table->date('monitoring_start_date')->nullable();
            $table->date('monitoring_end_date')->nullable();
            $table->text('monitoring_notes')->nullable();
            
            // Closure
            $table->foreignId('closed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('closed_at')->nullable();
            $table->text('closure_remarks')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capas');
    }
};
