<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gcash_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('transaction_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('reference');
            $table->string('receipt_path');
            $table->string('verification_status')->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            $table->timestamps();

            $table->index(['verification_status', 'created_at']);
            $table->index('reference');
        });

        Schema::create('cash_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('transaction_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('received_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        DB::table('transactions')
            ->where('payment_method', 'gcash')
            ->orderBy('id')
            ->each(function (object $transaction): void {
                DB::table('gcash_payments')->insert([
                    'transaction_id' => $transaction->id,
                    'reference' => $transaction->reference ?? '',
                    'receipt_path' => $transaction->receipt_path ?? '',
                    'verification_status' => match ($transaction->status) {
                        'paid' => 'verified',
                        'rejected' => 'rejected',
                        default => 'pending',
                    },
                    'verified_by' => $transaction->verified_by,
                    'verified_at' => $transaction->verified_at,
                    'verification_notes' => $transaction->verification_notes,
                    'created_at' => $transaction->created_at,
                    'updated_at' => $transaction->updated_at,
                ]);
            });

        DB::table('transactions')
            ->where('payment_method', 'cash')
            ->orderBy('id')
            ->each(function (object $transaction): void {
                DB::table('cash_payments')->insert([
                    'transaction_id' => $transaction->id,
                    'received_by' => null,
                    'received_at' => $transaction->status === 'paid' ? ($transaction->paid_at ?? $transaction->created_at) : null,
                    'notes' => null,
                    'created_at' => $transaction->created_at,
                    'updated_at' => $transaction->updated_at,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_payments');
        Schema::dropIfExists('gcash_payments');
    }
};
