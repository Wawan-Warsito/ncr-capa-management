<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ncrs', function (Blueprint $table) {
            $table->boolean('evaluation_sustainability_verified')->default(false)->after('effectiveness_verified');
            $table->boolean('evaluation_issue_closed_3months')->default(false)->after('evaluation_sustainability_verified');
            $table->boolean('ir_required')->nullable()->after('evaluation_issue_closed_3months');
            $table->string('ir_no', 255)->nullable()->after('ir_required');
            $table->boolean('customer_approval_reference')->default(false)->after('ir_no');
        });
    }

    public function down(): void
    {
        Schema::table('ncrs', function (Blueprint $table) {
            $table->dropColumn([
                'evaluation_sustainability_verified',
                'evaluation_issue_closed_3months',
                'ir_required',
                'ir_no',
                'customer_approval_reference',
            ]);
        });
    }
};

