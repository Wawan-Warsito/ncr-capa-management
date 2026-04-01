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
        if (!Schema::hasTable('ncrs')) {
            Schema::create('ncrs', function (Blueprint $table) {
                $table->id();
                $table->string('ncr_number', 50)->unique();
                
                // Project Information
                $table->string('order_number', 50)->nullable();
                $table->string('project_name', 200)->nullable();
                $table->string('customer_name', 200)->nullable();
                $table->text('product_description')->nullable();
                $table->string('drawing_number', 100)->nullable();
                $table->string('material_specification', 200)->nullable();
                
                // NCR Basic Info
                $table->date('date_found');
                $table->string('location_found', 200)->nullable();
                $table->integer('quantity_affected')->nullable();
                
                // Department Information
                $table->foreignId('finder_dept_id')->constrained('departments');
                $table->foreignId('receiver_dept_id')->constrained('departments');
                $table->foreignId('created_by_user_id')->constrained('users');
                
                // Defect Information
                $table->foreignId('defect_category_id')->constrained('defect_categories');
                $table->text('defect_description');
                $table->text('defect_location')->nullable();
                $table->foreignId('severity_level_id')->constrained('severity_levels');
                
                // Disposition
                $table->foreignId('disposition_method_id')->nullable()->constrained('disposition_methods')->nullOnDelete();
                $table->text('disposition_details')->nullable();
                $table->foreignId('disposition_approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('disposition_approved_at')->nullable();
                
                // Immediate Actions
                $table->text('immediate_action')->nullable();
                $table->text('containment_action')->nullable();
                
                // Cost Impact
                $table->decimal('estimated_cost', 15, 2)->nullable();
                $table->decimal('actual_cost', 15, 2)->nullable();
                
                // Status & Workflow
                $table->string('status')->default('Draft');
                
                // Approval Tracking
                $table->foreignId('finder_manager_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('finder_approved_at')->nullable();
                $table->text('finder_approval_remarks')->nullable();
                
                $table->foreignId('qc_manager_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('qc_registered_at')->nullable();
                $table->text('qc_registration_remarks')->nullable();
                
                $table->foreignId('receiver_manager_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('receiver_assigned_at')->nullable();
                $table->text('receiver_assignment_remarks')->nullable();
                
                $table->foreignId('assigned_pic_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('pic_assigned_at')->nullable();
                
                // ASME Specific
                $table->boolean('is_asme_project')->default(false);
                $table->string('asme_code_reference', 100)->nullable();
                $table->foreignId('ncr_coordinator_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('asme_reviewed_at')->nullable();
                $table->text('asme_review_remarks')->nullable();
                
                // Verification
                $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('verified_at')->nullable();
                $table->text('verification_remarks')->nullable();
                $table->boolean('effectiveness_verified')->default(false);
                
                // Closure
                $table->foreignId('closed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('closed_at')->nullable();
                $table->text('closure_remarks')->nullable();
                
                // Recurrence Tracking
                $table->boolean('is_recurring')->default(false);
                $table->foreignId('parent_ncr_id')->nullable()->constrained('ncrs')->nullOnDelete();
                $table->integer('recurrence_count')->default(0);
                
                // Timestamps
                $table->timestamp('submitted_at')->nullable();
                $table->date('target_closure_date')->nullable();
                $table->date('actual_closure_date')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ncrs');
    }
};
