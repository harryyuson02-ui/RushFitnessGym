<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->foreignId('requested_coach_id')->nullable()->after('membership_plan_id')->constrained('coaches')->nullOnDelete();
        });

        DB::statement(<<<'SQL'
            UPDATE subscriptions
            SET requested_coach_id = (
                SELECT members.coach_id
                FROM members
                WHERE members.id = subscriptions.member_id
            )
            WHERE status = 'pending'
              AND EXISTS (
                SELECT 1
                FROM members
                WHERE members.id = subscriptions.member_id
                  AND members.coach_id IS NOT NULL
              )
        SQL);

        DB::table('members')
            ->whereExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('subscriptions')
                    ->whereColumn('subscriptions.member_id', 'members.id')
                    ->where('subscriptions.status', 'pending');
            })
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('subscriptions')
                    ->whereColumn('subscriptions.member_id', 'members.id')
                    ->where('subscriptions.status', 'active')
                    ->whereDate('subscriptions.expires_at', '>=', now()->toDateString());
            })
            ->update(['coach_id' => null]);
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('requested_coach_id');
        });
    }
};
