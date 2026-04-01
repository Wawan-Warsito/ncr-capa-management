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
        Schema::create('capa_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('capa_id')->constrained('capas')->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path');
            $table->integer('file_size');
            $table->string('file_type')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('attachment_type')->default('Evidence'); // Evidence, Report, Other
            $table->text('description')->nullable();
            $table->foreignId('uploaded_by_user_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('uploaded_at')->useCurrent();
            $table->boolean('is_deleted')->default(false);
            $table->dateTime('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capa_attachments');
    }
};
