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
        Schema::create('support_resources', function (Blueprint $table) {
            $table->id();
            $table->string('organization_name');
            $table->string('resource_type');
            $table->text('description');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('address')->nullable();
            $table->string('location');
            $table->string('availability');
            $table->string('status')->default('inactive');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'resource_type']);
            $table->index('location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_resources');
    }
};
