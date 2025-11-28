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
        Schema::create('readme_documents', function (Blueprint $table) {
            $table->id();
            $table->string('version')->index();
            $table->string('page')->index();
            $table->string('title')->nullable();
            $table->text('content');
            $table->text('html_content')->nullable();
            $table->string('file_path');
            $table->string('file_hash');
            $table->timestamp('indexed_at')->nullable();
            $table->timestamps();

            $table->index(['version', 'page']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('readme_documents');
    }
};
