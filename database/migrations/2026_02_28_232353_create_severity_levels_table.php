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
        if (!Schema::hasTable('severity_levels')) {
            Schema::create('severity_levels', function (Blueprint $table) {
                $table->id();
                $table->string('level_name', 50);
                $table->string('level_code', 20)->unique();
                $table->integer('priority');
                $table->string('color_code', 20)->nullable();
                $table->text('description')->nullable();
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
        Schema::dropIfExists('severity_levels');
    }
};
