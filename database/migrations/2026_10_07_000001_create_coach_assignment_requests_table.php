<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coach_assignment_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('coach_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['coach_id', 'status']);
            $table->index(['member_id', 'status']);
        });

        DB::statement("CREATE UNIQUE INDEX coach_assignment_requests_one_pending_per_member ON coach_assignment_requests (member_id) WHERE status = 'pending'");
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_assignment_requests');
    }
};
