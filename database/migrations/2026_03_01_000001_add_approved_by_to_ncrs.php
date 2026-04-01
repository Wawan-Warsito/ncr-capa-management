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
            if (!Schema::hasColumn('ncrs', 'approved_by_user_id')) {
                $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('ncrs', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ncrs', function (Blueprint $table) {
            $table->dropForeign(['approved_by_user_id']);
            $table->dropColumn(['approved_by_user_id', 'approved_at']);
        });
    }
};
