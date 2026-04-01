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
        Schema::create('defect_modes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('defect_category_id')->nullable()->constrained('defect_categories')->onDelete('cascade'); // Link to category (Defect Group)
            $table->string('mode_name'); // "Defect Mode Name"
            $table->string('mode_description')->nullable(); // Optional full description
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('defect_modes');
    }
};
