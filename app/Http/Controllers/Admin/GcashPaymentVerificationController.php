<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyGcashPaymentRequest;
use App\Models\Transaction;
use App\Notifications\ApprovalDecision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class GcashPaymentVerificationController extends Controller
{
    public function __invoke(VerifyGcashPaymentRequest $request, Transaction $transaction): RedirectResponse
    {
        $decision = $request->validated('decision');

        $transaction = DB::transaction(function () use ($request, $transaction, $decision): Transaction {
            $lockedTransaction = Transaction::query()
                ->with(['member.user', 'gcashPayment'])
                ->whereKey($transaction->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $gcashPayment = $lockedTransaction->gcashPayment;

            abort_unless($lockedTransaction->payment_method === 'gcash' && $gcashPayment !== null, 422, 'Only GCash payments require this verification.');
            abort_unless($gcashPayment->verification_status === 'pending', 409, 'This GCash payment has already been reviewed.');
            abort_if(
                $decision === 'verified' && ($gcashPayment->reference === '' || $gcashPayment->receipt_path === ''),
                422,
                'A GCash reference and receipt are required before verification.',
            );

            $lockedTransaction->update([
                'status' => $decision === 'verified' ? 'paid' : 'rejected',
                'paid_at' => $decision === 'verified' ? now() : null,
            ]);
            $gcashPayment->update([
                'verification_status' => $decision,
                'verified_by' => $request->user()->id,
                'verified_at' => now(),
                'verification_notes' => $request->validated('verification_notes'),
            ]);

            return $lockedTransaction;
        });

        $transaction->member->user?->notify(new ApprovalDecision(
            'GCash payment '.($decision === 'verified' ? 'verified' : 'rejected'),
            $decision === 'verified'
                ? 'Your GCash payment was verified. Your membership application is ready for final approval.'
                : 'Your GCash payment could not be verified. Please contact the gym before submitting another application.',
            'member.dashboard',
        ));

        return redirect()->route('admin.transactions.index')->with(
            'success',
            'GCash payment '.($decision === 'verified' ? 'verified successfully.' : 'rejected.'),
        );
    }
}
