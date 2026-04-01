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
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('role_name')->unique();
                $table->string('display_name')->nullable(); // Made nullable to match SQL loose structure if needed, though SQL said NOT NULL
                $table->text('description')->nullable();
                $table->json('permissions')->nullable();
                $table->integer('level')->default(1)->comment('1=Staff, 2=Supervisor, 3=Manager, 4=QC Manager, 5=Admin');
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
        Schema::dropIfExists('roles');
    }
};
