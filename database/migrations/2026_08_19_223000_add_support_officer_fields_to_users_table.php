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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('gender')->nullable()->after('phone');
            $table->string('staff_id')->nullable()->unique()->after('account_status');
            $table->string('specialization')->nullable()->after('staff_id')->index();
            $table->date('date_joined')->nullable()->after('specialization');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_staff_id_unique');
            $table->dropIndex('users_specialization_index');
            $table->dropColumn([
                'phone',
                'gender',
                'staff_id',
                'specialization',
                'date_joined',
            ]);
        });
    }
};
