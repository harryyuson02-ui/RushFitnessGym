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
        Schema::table('members', function (Blueprint $table) {
            $table->string('fitness_goal')->nullable()->after('preferred_channel');
            $table->json('availability_days')->nullable()->after('fitness_goal');
            $table->time('availability_starts_at')->nullable()->after('availability_days');
            $table->time('availability_ends_at')->nullable()->after('availability_starts_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn([
                'fitness_goal',
                'availability_days',
                'availability_starts_at',
                'availability_ends_at',
            ]);
        });
    }
};
