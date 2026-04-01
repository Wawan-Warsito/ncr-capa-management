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
        Schema::table('ncrs', function (Blueprint $table) {
            // Header Info
            $table->string('line_no')->nullable()->after('ncr_number');
            $table->date('issued_date')->nullable()->after('date_found'); // "Date of NCR issued"
            $table->string('last_ncr_no')->nullable()->after('ncr_number');
            
            // Project Info
            $table->string('project_sn')->nullable()->after('order_number'); // "Project / SN"

            // Part Info
            $table->string('part_name')->nullable()->after('product_description');
            
            // Defect Details
            $table->string('defect_mode')->nullable()->after('defect_description');
            
            // Cost Analysis (USD)
            $table->decimal('mh_used', 8, 2)->nullable()->after('actual_cost');
            $table->decimal('mh_rate', 8, 2)->nullable()->after('mh_used');
            $table->decimal('labor_cost', 15, 2)->nullable()->after('mh_rate');
            $table->decimal('material_cost', 15, 2)->nullable()->after('labor_cost');
            $table->decimal('subcont_cost', 15, 2)->nullable()->after('material_cost');
            $table->decimal('engineering_cost', 15, 2)->nullable()->after('subcont_cost');
            $table->decimal('other_cost', 15, 2)->nullable()->after('engineering_cost');
            $table->decimal('total_cost', 15, 2)->nullable()->after('other_cost');
            
            // CA Progress
            $table->date('ca_finish_date')->nullable()->after('immediate_action');
            $table->integer('days_passed')->nullable()->after('ca_finish_date');
            
            // RCA & PA (Directly on NCR for this specific form view)
            $table->text('root_cause')->nullable()->after('defect_description');
            $table->text('preventive_action')->nullable()->after('root_cause');
            
            // Related Docs
            $table->string('related_document')->nullable()->after('ncr_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ncrs', function (Blueprint $table) {
            $table->dropColumn([
                'line_no',
                'issued_date',
                'last_ncr_no',
                'project_sn',
                'part_name',
                'defect_mode',
                'mh_used',
                'mh_rate',
                'labor_cost',
                'material_cost',
                'subcont_cost',
                'engineering_cost',
                'other_cost',
                'total_cost',
                'ca_finish_date',
                'days_passed',
                'root_cause',
                'preventive_action',
                'related_document'
            ]);
        });
    }
};
