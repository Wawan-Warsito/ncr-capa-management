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
        if (!Schema::hasTable('disposition_methods')) {
            Schema::create('disposition_methods', function (Blueprint $table) {
                $table->id();
                $table->string('method_name', 50);
                $table->string('method_code', 20)->unique();
                $table->text('description')->nullable();
                $table->boolean('requires_approval')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disposition_methods');
    }
};
