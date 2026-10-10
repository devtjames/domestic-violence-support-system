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
        Schema::create('anonymous_case_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_case_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('access_code_hash');
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamps();

            $table->index('last_accessed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anonymous_case_accesses');
    }
};
